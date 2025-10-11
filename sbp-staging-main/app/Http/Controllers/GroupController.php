<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        $groups = $organisation->groups()->with('users')->get();
        
        // Get all users in the organization for the create form
        $users = $organisation->employees()->with('user')->get()->map(function ($employee) {
            return $employee->user;
        });

        return Inertia::render('Organisation/Group/Index', [
            'groups' => $groups,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('create', Organisation::class)) {
            return abort(404);
        }

        // Get all users in the organization
        $users = $organisation->employees()->with('user')->get()->map(function ($employee) {
            return $employee->user;
        });

        return Inertia::render('Organisation/Group/Create', [
            'organisation' => $organisation,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('update', $organisation)) {
            return abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group = Group::create([
            'name' => $request->input('name'),
            'organisation_id' => $organisation->id,
        ]);

        // Attach users to the group
        if ($request->has('user_ids')) {
            $group->users()->attach($request->input('user_ids'));
        }

        return redirect()->back()->with('message', ['status' => 'success', 'message' => 'Group created successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Group $group)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('view', $organisation) || $organisation->id !== $group->organisation_id) {
            return abort(404);
        }

        $group->load('users');

        return Inertia::render('Organisation/Group/View', [
            'organisation' => $organisation,
            'group' => $group,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Group $group)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('update', $organisation)) {
            return abort(404);
        }

        $group->load('users');
        
        // Get all users in the organization for the edit form
        $users = $organisation->employees()->with('user')->get()->map(function ($employee) {
            return $employee->user;
        });

        return Inertia::render('Organisation/Group/Edit', [
            'organisation' => $organisation,
            'group' => $group,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('update', $organisation)) {
            return abort(401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group->name = $request->name;
        $group->save();

        // Sync users to the group
        if ($request->has('user_ids')) {
            $group->users()->sync($request->input('user_ids'));
        } else {
            $group->users()->detach();
        }

        return redirect()->back()->with('message', ['status' => 'success', 'message' => 'Group updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Group $group)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('delete', $organisation)) {
            return abort(401);
        }

        $group->delete();

        return redirect()->back();
    }

    /**
     * Get all groups for the organization (API endpoint)
     */
    public function getAllGroups(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        $groups = $organisation->groups()->with('users')->get();

        return response([
            "groups" => $groups,
        ]);
    }
}
