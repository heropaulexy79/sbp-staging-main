<?php

namespace App\Http\Controllers;

use App\Events\EmployeeInvited;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganisationController extends Controller
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
    public function store(Request $request)
    {
        $user = $request->user();
        // $user->organisation_id = $user->organisationNew

        if ($user->organisationNew?->organisation_id) {
            return redirect()->back(400)->with('global:message', [
                'status' => 'error',
                'message' => 'Account is already a member of an organisation.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organisation = Organisation::create([
            'name' => $request->input('name'),
        ]);


        $organisation = OrganisationUser::create([
            // 'name' => $request->input('name'),
            'user_id' => $user->id,
            'organisation_id' => $organisation->id,
            'role' => User::ROLE_ADMIN,
        ]);

        return redirect()->back()->with('global:message', [
            'status' => 'success',
            'message' => 'Organisation has been created. You can now invite members to your organisation!',
            'action' => [
                'cta:link' => route('organisation.edit'),
                'cta:text' => 'Invite',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organisation $organisation)
    {
        //
    }

    public function getAllEmployees(Request $request)
    {
        //
        $user = $request->user();


        return response([
            "students" => $user->organisationNew->organisation->employees()->with("user:id,name,email")->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(404);
        }

        return Inertia::render('Organisation/Edit', [
            'organisation' => $user->organisationNew->organisation,
            'employees' => $user->organisationNew->organisation->employees()->with("user:id,name,email")->get(),
            'invites' => $user->organisationNew->organisation->invites,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation)
    {
        //
        $user = $request->user();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organisation->name = $request->name;

        $organisation->save();

        return redirect()->back()->with('global:message', [
            'status' => 'success',
            'message' => 'Changes have been saved!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organisation $organisation)
    {
        //
    }


    public function uninviteEmployee(Request $request, Organisation $organisation, OrganisationInvitation $invitation)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $invitation->delete();

        // "global:message", [
        //     "status" => "success",
        //     "message" => "Uninvited",
        // ]
        return back();
    }
}
