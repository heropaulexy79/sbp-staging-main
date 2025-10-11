<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationUser;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {

        $invitationEmail = '';

        if ($request->has('tk')) {
            $invitation = OrganisationInvitation::where('token', $request->get('tk'))->first();
            $invitationEmail = $invitation->email ?? '';
        }

        return Inertia::render('Auth/Register', [
            'prefilled' => [
                'email' => $invitationEmail,
            ],
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invitation_token' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // from verify-email controller
        if ($request->invitation_token) {

            $invitation = OrganisationInvitation::where('token', $request->invitation_token)->first();

            if (!$invitation) {
                // abort invitation expired
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            // $org = Organisation::find($invitation->organisation_id);

            // $user->organisation_id = $invitation->organisation_id;
            // $user->role = $invitation->role ?? 'STUDENT';

            OrganisationUser::create([
                "user_id" => $user->id,
                "organisation_id" => $invitation->organisation_id,
                "role" => $invitation->role ?? 'STUDENT'
            ]);

            $user->save();

            $invitation->delete();
        }

        event(new Registered($user));

        // if (!$request->invitation_token) {
        Mail::to($user->email)->queue(new WelcomeMail($user));
        // }


        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
