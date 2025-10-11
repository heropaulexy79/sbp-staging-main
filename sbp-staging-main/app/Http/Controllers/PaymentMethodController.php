<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($data)
    {
        PaymentMethod::Where('organisation_id', $data['organisation_id'])->delete();

        PaymentMethod::create([
            "auth_code" => $data['auth_code'],
            "first_six" => $data['first_six'],
            "last_four" => $data['last_four'],
            "exp_month" => $data['exp_month'],
            "exp_year" => $data['exp_year'],
            "card_type" => $data['card_type'],
            "bank" => $data['bank'],
            "country" => $data['country'],
            "reusable" => $data['reusable'] ?? true,
            "account_name" => $data['account_name'],
            "organisation_id" => $data['organisation_id'],
            "email_address" => $data['email_address'],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($data)
    {
        //
        return PaymentMethod::Where('organisation_id', $data['organisation_id'])
            ->Where('auth_code', $data['auth_code']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        //
    }
}
