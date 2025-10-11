<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;


class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if (!$user->isAdminInOrganisation($organisation)) {
            abort(404);
        }

        $search = strip_tags($request->query('search') ?? '');

        //
        if (empty($search)) {
            $courses = Course::where('is_published', true)
                ->paginate();
        } else {
            $courses = Course::where('is_published', true)
                ->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                })
                ->paginate();
        }

        // dd($search, empty($search), $courses);

        return Inertia::render('Course/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Course $course)
    {

        $user = $request->user();
        $organisation = $user->organisation();

        // If it is public allow?
        if (!$course->is_published || !$user->isAdminInOrganisation($organisation)) {
            abort(404);
        }

        // dd($course->enrolledUsers()->count());
        // dd($course->lessons()->published()->get());

        return Inertia::render('Course/View', [
            'course' => $course,
            'enrolled_count' => $course->enrolledUsers()->count(),
            'lessons' => $course->lessons()->published()->get(['title', 'position']),
        ]);
    }
}
