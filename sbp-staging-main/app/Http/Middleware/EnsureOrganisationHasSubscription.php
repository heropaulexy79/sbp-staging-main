<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganisationHasSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $org = $user?->organisationNew?->organisation;

        if (!$org) {
            return $next($request);
        }


        $sub = $org->activeSubscription();

        if (!$sub) {
            return redirect('/settings/subscription');
        }


        return $next($request);
    }
}
