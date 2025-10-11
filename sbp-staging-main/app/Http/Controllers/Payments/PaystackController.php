<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\BillingController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\SubscriptionController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Log;
use Paystack;
use Throwable;

class PaystackController extends Controller
{
    /**
     * Redirect the User to Paystack Payment Page
     * @return Url
     */
    public function redirectToGateway(Request $request)
    {
        try {
            // dd(Paystack::getAuthorizationUrl()->url);
            /** @disregard */
            return Inertia::location(Paystack::getAuthorizationUrl()->url);
        } catch (\Exception $e) {
            return redirect()->back()->with('message', [
                'status' => 'error',
                'message' => 'The paystack token has expired. Please refresh the page and try again.'
            ]);
        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();

        // dd($paymentDetails);
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
        if (
            ($paymentDetails['data']['metadata']['type'] === 'ADD-PAYMENT-METHOD' ||
                $paymentDetails['data']['metadata']['type'] === 'ADD_PAYMENT_METHOD') &&
            $paymentDetails['data']['status'] === 'success'
        ) {

            $sc = new SubscriptionController();
            $pm = new PaymentMethodController();

            $pm->store(
                array(
                    "auth_code" => $paymentDetails['data']['authorization']['authorization_code'],
                    "first_six" => $paymentDetails['data']['authorization']['bin'],
                    "last_four" => $paymentDetails['data']['authorization']['last4'],
                    "exp_month" => $paymentDetails['data']['authorization']['exp_month'],
                    "exp_year" => $paymentDetails['data']['authorization']['exp_year'],
                    "card_type" => $paymentDetails['data']['authorization']['card_type'],
                    "bank" => $paymentDetails['data']['authorization']['bank'],
                    "country" => $paymentDetails['data']['authorization']['country_code'],
                    "reusable" => $paymentDetails['data']['authorization']['reusable'],
                    "account_name" => $paymentDetails['data']['authorization']['account_name'],
                    "organisation_id" => $paymentDetails['data']['metadata']['organisation_id'],
                    "email_address" => $paymentDetails['data']['customer']['email'],
                )
            );


           $selectedPlan = $paymentDetails['data']['metadata']['selected_plan'] ?? '';
            if ($selectedPlan != '') {
                $sc->createSub([
                    "selected_plan" => $selectedPlan,
                    "organisation_id" => $paymentDetails['data']['metadata']['organisation_id'],
                    "transaction_ref" => $paymentDetails['data']['reference'],
                ]);
            }

            return redirect(route(
                'organisation.billing.index'
            ))->with('global:message', [
                'status' => 'success',
                'message' => 'Payment method added successfully!',
            ]);
        }


        return abort(404);
    }


    public function handleWebhook(Request $request)
    {
        // Only process POST requests with Paystack signature header
        if ($request->method() !== 'POST' || !$request->hasHeader('HTTP_X_PAYSTACK_SIGNATURE')) {
            return response()->json([], 401); // Unauthorized
        }

        $input = $request->getContent();
        $secretKey = config('paystack.secretKey'); // Assuming SECRET_KEY is stored in config/paystack.php

        // Validate event signature to prevent tampering
        $expectedSignature = Hash::make('sha512', $input, $secretKey);
        if ($request->header('HTTP_X_PAYSTACK_SIGNATURE') !== $expectedSignature) {
            return response()->json([], 403); // Forbidden
        }

        $event = json_decode($input);

        $sc = new SubscriptionController();
        $bh = new BillingController();
        $pm = new PaymentMethodController();


        // Handle for charge
        if ($event['event']["charge.success"]) {
            // 
            $existingHistory = $bh->show($event['data']['reference']);
            $existingPaymentMethod = $pm->show(array(
                "auth_code" => $event['data']['authorization']['authorization_code'],
                "organisation_id" => $event['data']['metadata']['organisation_id'],
            ));

            $type = $event['data']['metadata']['type'];

            if (!$existingHistory) {
                $bh->store(array(
                    "transaction_ref" => $event['data']['reference'],
                    "currency" => $event['data']['currency'],
                    "amount" => $event['data']['amount'] / 100, // this is because paystack stores in kobo
                    "description" => $event['data']['metadata']['description'] ?? "Add payment method",
                    "provider" => "PAYSTACK",
                    "organisation_id" => $event['data']['metadata']['organisation_id'],
                ));
            }

            if (!$existingPaymentMethod && ($type === "ADD_PAYMENT_METHOD" || $type === "ADD-PAYMENT-METHOD")) {
                $pm->store(
                    array(
                        "auth_code" => $event['data']['authorization']['authorization_code'],
                        "first_six" => $event['data']['authorization']['bin'],
                        "last_four" => $event['data']['authorization']['last4'],
                        "exp_month" => $event['data']['authorization']['exp_month'],
                        "exp_year" => $event['data']['authorization']['exp_year'],
                        "card_type" => $event['data']['authorization']['card_type'],
                        "bank" => $event['data']['authorization']['bank'],
                        "country" => $event['data']['authorization']['country_code'],
                        "reusable" => $event['data']['authorization']['reusable'],
                        "account_name" => $event['data']['authorization']['account_name'],
                        "organisation_id" => $event['data']['metadata']['organisation_id'],
                        "email_address" => $event['data']['customer']['email'],
                    )
                );


                $org = \App\Models\Organisation::where('id', '=', $event['data']['metadata']['organisation_id'])->first();


                if (!$org->activeSubscription()) {
                    $selectedPlan = $event['data']['metadata']['selected_plan'] ?? '';
                    if ($selectedPlan != '') {
                        $sc->createSub([
                            "selected_plan" => $selectedPlan,
                            "organisation_id" => $event['data']['metadata']['organisation_id'],
                        ]);
                    }
                }
            }

            if ($type === 'SUBSCRIPTION') {
                if (!$org->activeSubscription()) {
                    $selectedPlan = $event['data']['metadata']['plan'] ?? '';
                    $sc->createSub([
                        "selected_plan" => $selectedPlan,
                        "organisation_id" => $event['data']['metadata']['organisation_id'],
                    ]);
                }
            }
        }


        return response()->json([], 200);
    }

    public function initiateRefund($data)
    {
        $secretKey = config('paystack.secretKey'); // Assuming SECRET_KEY is stored in config/paystack.php
        $baseUrl = 'https://api.paystack.co/refund';

        $client = new Client();

        $headers = [
            'Authorization' => 'Bearer ' . $secretKey,
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
        ];

        $data = [
            'transaction' => $data['transaction'],
            'amount' => $data['amount'],
        ];

        try {
            $response = $client->post($baseUrl, [
                'headers' => $headers,
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $bh = new BillingController();

            if ($statusCode >= 200 && $statusCode < 300) {
                $event = json_decode($responseBody);

                $existingHistory = $bh->show($event['data']['reference']);

                if ($existingHistory) {
                    $existingHistory->description = "{$existingHistory->description} (Refunded)";
                    $existingHistory->save();
                }

                // Successful refund initiation
                return response()->json(json_decode($responseBody), $statusCode);
            } else {
                // Handle error response
                return response()->json(json_decode($responseBody), $statusCode);
            }
        } catch (Throwable $e) {
            // Handle exceptions during the request
            return response()->json([
                'message' => 'An error occurred during the refund request.',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }


    public function chargeCard($data)
    {
        $paymentMethod = $data['payment_method'];
        $amount = $data['amount'];
        $currency = $data['currency'];
        $metadata = $data['metadata'];


        $secretKey = config('paystack.secretKey'); // Assuming SECRET_KEY is stored in config/paystack.php
        $paystackUrl = config('paystack.paymentUrl');
        $baseUrl = $paystackUrl . '/transaction/charge_authorization';

        $client = new Client();

        $headers = [
            'Authorization' => 'Bearer ' . $secretKey,
            'Content-Type' => 'application/json',
        ];

        // Log::info($paymentMethod);

        $data = [
            'authorization_code' => $paymentMethod->auth_code,
            'email' => $paymentMethod->email_address, // Get customer email from request
            'amount' => $amount,
            'currency' => $currency,
            'metadata' => $metadata,
        ];


        $response = $client->post($baseUrl, [
            'headers' => $headers,
            'json' => $data,
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        if ($statusCode >= 200 && $statusCode < 300) {
            $event = json_decode($responseBody, true);
            return [
                "gateway" => "PAYSTACK",
                'event' => $event
            ];
        } else {
            Log::error([
                'data' => json_decode($responseBody),
                'status' => $statusCode
            ]);

            return null;
        }
    }


    public function verifyPayment()
    {
        // 
    }
}
