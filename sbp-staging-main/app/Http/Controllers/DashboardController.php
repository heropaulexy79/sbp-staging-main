<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //

    public function index(Request $request)
    {

        $user = $request->user();

        if ($user->account_type === "TEACHER") {
            return Inertia::render(
                'Teacher/TeacherDashboard',
                [
                    'courses' => $request->user()->createdCourses()->paginate()
                ]
            );
        }


        $status = $request->query('status');

        switch ($status) {
            case 'all_enrolled':
                $courses = Course::whereHas('enrolledUsers.organisationNew', function ($query) use ($user) {
                    $query->where('organisation_id', $user->organisationNew->organisation_id);
                    $query->where('is_published', true);
                })->paginate()->withQueryString();
                break;

            default:
                $courses = $request->user()->enrolledCourses()->where("is_completed", $status === "completed" ? "1" : "0")->where("is_published", true)
                    ->paginate()->withQueryString();
                break;
        }


        return Inertia::render(
            'Organisation/OrganisationDashboard',
            ['courses' => $courses]
        );
    }
}
