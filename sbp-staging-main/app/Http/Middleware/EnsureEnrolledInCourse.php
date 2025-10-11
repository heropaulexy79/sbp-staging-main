<?php

namespace App\Http\Middleware;

use App\Models\Course;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolledInCourse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->user();


        $course = $request->route('course');


        if (is_string($course)) {
            $course = Course::where('slug', $course)->firstOrFail();
        }

        if (!$course || !$course->is_published  || (!$user->isEnrolledInCourse($course->id) && $user->cannot('update', $course->organisation))) {

            // if ($course->is_public) {
            //     return redirect(route('public.course.show', ["course" => $course->id]));
            // }
            return abort(404);
            // return abort(403, 'Unauthorized Access: Not Enrolled in Course'); // Abort with 403 (Forbidden)
        }

        return $next($request);
    }
}
