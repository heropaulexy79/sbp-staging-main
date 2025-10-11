<?php

namespace App\Http\Controllers;

use App\Events\EmployeeInvited;
use App\Http\Requests\Organisation\InviteToOrganisationRequest;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationUser;
use App\Models\User;
use Illuminate\Http\Request;

class OrganisationUserController extends Controller
{
    //
    public function index(Request $request)
    {
        //
        $user = $request->user();


        return response([
            "students" => $user->organisationNew->organisation->employees()->with("user:id,name,email")->get(),
        ]);
    }

    public  function store(InviteToOrganisationRequest $request, Organisation $organisation)
    {
        $invites = [];



        foreach ($request->input("invites") as $key => $value) {

            $invites[] = [
                'email' => $value["email"],
                'organisation_id' => $organisation->id,
                'token' => OrganisationInvitation::generateUniqueToken(),
                'role' => $request->input('role', 'STUDENT'),
            ];
        }



        OrganisationInvitation::insert($invites);

        $invitesEmails = array_column($invites, 'email');
        $invitations = OrganisationInvitation::whereIn('email', $invitesEmails)->get();

        // dd($invitations);

        foreach ($invitations as $invitation) {
            // Send Notification
            event(new EmployeeInvited($invitation));
        }


        return back()->with('success', 'Employee invitations sent successfully!');
    }


    public function update(Request $request, Organisation $organisation, User $employee)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $request->validate([
            'role' => 'required|in:STUDENT,ADMIN',
        ]);

        $employee_org_profile = $employee->organisationNew;

        if (!$employee_org_profile || $employee_org_profile->organisation_id !== $organisation->id) {
            return back()->with('global:message', [
                'status' => 'error',
                'message' => 'Employee not found!',
            ]);
        }

        $employee_org_profile->role = $request->role;
        $employee_org_profile->save();

        return back();
    }


    public function destroy(Request $request, Organisation $organisation, User $employee)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $employee_user = $organisation->employees()->where('user_id', $employee->id);

        $employee_user->delete();
        $employee->delete();

        // "global:message", [
        //     "status" => "success",
        //     "message" => "Uninvited",
        // ]
        return back();
    }



    public function inviteEmployee(Request $request, Organisation $organisation)
    {

        $user = $request->user();

        if (!$user->isAdminInOrganisation($user->organisationNew->organisation)) {
            return abort(401, "You don't have permission to make this request");
        }

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'nullable|in:STUDENT,ADMIN',
        ]);

        $invitation = OrganisationInvitation::create([
            'email' => $request->input('email'),
            'organisation_id' => $organisation->id,
            'token' => OrganisationInvitation::generateUniqueToken(),
            'role' => $request->input('role', 'STUDENT'),
        ]);

        // Send Notification
        event(new EmployeeInvited($invitation));

        return back()->with('success', 'Employee invitation sent successfully!');
    }
}
