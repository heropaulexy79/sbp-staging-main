<?php

namespace App\Http\Controllers;

use App\Events\SubscriptionBillingFailed;
use App\Http\Controllers\Payments\PaystackController;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SubscriptionController extends Controller
{

    public function index(Request $request)
    {
        $org = $request->user()->organisationNew->organisation;
        $subscription = $org->activeSubscription();

        return Inertia::render('Organisation/Billing/Index', [
            "payment_method" => $org->paymentMethods->first(),
            "subscription" => $subscription,
            "history" => $org->billingHistories ?? []
        ]);
    }



    public function show(Request $request)
    {
        $user = $request->user();
        $org = $user->organisationNew->organisation;



        return Inertia::render('Organisation/Subscription/Index', [
            "plans" => config('subscriptions.plans'),
            "payment_method" => $org->paymentMethods->first(),
            "is_admin" => $user->isAdminInOrganisation($org),
        ]);
    }


    //
    public function store(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }



        if ($organisation->activeSubscription()) {
            return redirect()->back()->with('global:message', [
                'status' => 'error',
                'message' => 'You already have an existing subscriptions.',
            ]);
        }


        $request->validate([
            'plan' => 'required|in:starter,enterprise',
        ]);

        $subscription = $this->createSub([
            "selected_plan" => $request->plan,
            "organisation_id" => $organisation->id,
        ]);

        // $plan = config("subscriptions.plans.{$request->plan}");

        // if ($request->plan === 'enterprise') {
        //     $amount = $this->getCustomEnterprisePrice($request);
        // } else {
        //     $amount = $plan['price'];
        // }

        // $subscription = Subscription::create([
        //     'organization_id' => $organisation->id,
        //     'plan' => $request->plan,
        //     'status' => 'active',
        //     'next_billing_date' => now()->addMonth(),
        //     'amount' => $amount,
        // ]);


        return redirect()->intended(route('dashboard'))->with('global:message', [
            'status' => 'success',
            'message' => 'Subscription created!',
        ]);
    }


    public function destroy(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        $sub = $organisation->activeSubscription();

        $sub->update([
            'status' => 'cancelled',
            'next_billing_date' => null,
        ]);


        return redirect()->back()->with('global:message', [
            'status' => 'success',
            'message' => 'Subscription cancelled!',
        ]);
    }

    function createSub($data)
{
    $selectedPlan = $data['selected_plan'];
    $organisationId = $data['organisation_id'];
    $transactionRef = $data['transaction_ref'] ?? null;

    $plan = config("subscriptions.plans.{$selectedPlan}");

    if ($plan['id'] === 'enterprise') {
        $amount = $this->getCustomEnterprisePrice();
    } else {
        $amount = $plan['price'];
    }

    $subscription = Subscription::create([
        'organisation_id' => $organisationId,
        'plan' => $plan['id'],
        'status' => 'active',
        'next_billing_date' => now()->addMonth(),
        'amount' => $amount,
        'description' => '',
        'transaction_ref' => $transactionRef,
    ]);

    return $subscription;
}


    function chargeSubscription(Subscription $subscription)
    {
        // Call Paystack API to charge the user
        $plan = config("subscriptions.plans.{$subscription->plan}");
        $org  = $subscription->organisation;
        $orgCount = $org->employees->count();
        $paymentMethod = $org->paymentMethods->first();

        // First sub billed at is null
        $billedAt = $subscription->billed_at ? $subscription->billed_at->format('Y-m-d') : null;

        if (!$billedAt) {
            $billedAt = $subscription->created_at->format('Y-m-d');
        }

        $nextBillDate = $subscription->next_billing_date->format('Y-m-d');

        // TODO: Make factory to pick gateway based on payment method provider / plan currency
        $pg = new PaystackController();

        $resp = $pg->chargeCard([
            "payment_method" => $paymentMethod,
            "amount" => $orgCount * ($subscription->amount),
            "currency" => $plan['currency'],
            "metadata" => [
                "type" => "SUBSCRIPTION",
                "plan" => $plan['id'],
                "description" => "Subscription ({$plan['name']}) for {$billedAt} - {$nextBillDate}",
                "organisation_id" => $org->id,
            ]
        ]);

        $event = $resp['event'];

        // Log::debug("event", ["event" => $event]);

        if (!$event) {
            event(new SubscriptionBillingFailed($org));

            Log::error([
                'message' => 'An error occurred during the authorization request.',
                // 'exception' => $e->getMessage(),
                'status' => 500,
            ]);

            return;
        }

        $bh = new \App\Http\Controllers\BillingController();
        $bh->store(array(
            "transaction_ref" => $event['data']['reference'],
            "currency" => $event['data']['currency'],
            "amount" => $event['data']['amount'] / 100, // this is because paystack stores in kobo
            "description" => $event['data']['metadata']['description'] ?? "Subscription",
            "provider" => $resp['gateway'],
            "organisation_id" => $event['data']['metadata']['organisation_id'],
        ));

        $subscription->update([
            'billed_at' => now(),
            'next_billing_date' => now()->addMonth(),
        ]);
    }


    private function getCustomEnterprisePrice()
    {
        // Logic to get custom price for enterprise plan
        return config('subscriptions.plans.enterprise.price', 100);
        // For example, you could ask the user for a price in the request
        // return $request->custom_price; // Ensure to validate this input
    }
}
