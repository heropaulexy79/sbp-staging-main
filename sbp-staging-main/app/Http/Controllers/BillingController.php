<?php

namespace App\Http\Controllers;

use App\Models\BillingHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        //
        BillingHistory::create(array(
            "transaction_ref" => $data['transaction_ref'],
            "amount" => $data['amount'],
            "description" => $data['description'],
            "provider" => $data['provider'] ?? "PAYSTACK",
            "organisation_id" => $data['organisation_id'],
        ));
    }

    public function show(String $transaction_ref)
    {
        //
        return BillingHistory::find($transaction_ref);
    }


    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, BillingHistory $billingHistory)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(BillingHistory $billingHistory)
    // {
    //     //
    // }
}
