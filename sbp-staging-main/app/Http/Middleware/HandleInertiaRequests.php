<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $org = $user?->organisationNew?->organisation;
        // $org = $user?->organisation;

        $has_payment_method =  true;
        $hasActiveSubscription = true;

        if ($org && $user->account_type === 'ORG') {
            $has_payment_method = $org->paymentMethods->count() > 0;
            $hasActiveSubscription = $org->activeSubscription() != null;
        }



        if ($user) {
            $user->organisation_id = $user?->organisationNew?->organisation_id;
            $user->role = $user?->organisationNew?->role;
            // $user->organisation_name = $org?->name;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user?->makeHidden(['organisation']),
            ],
            'global' => [
                'has_payment_method' => $has_payment_method,
                'hasActiveSubscription' => $hasActiveSubscription,
            ],
            'query' => $request->query(),
            'flash' => [
                'global:message' => fn() => $request->session()->get('global:message'),
                'message' => fn() => $request->session()->get('message'),
            ],
        ];
    }
}
