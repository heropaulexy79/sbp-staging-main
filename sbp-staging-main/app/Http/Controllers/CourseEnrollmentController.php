<?php

namespace App\Http\Controllers;

// use App\Models\CourseEnrollment;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Group;
use App\Models\User;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseEnrollmentController extends Controller
{

    // TODO MOVE TO Namespaced Organisation/Course
    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        $organisation = $user->organisation();


        $enrolledUsers  = $course->enrolledUsers()->whereHas('organisationNew', function ($query) use ($organisation) {
            $query->where('organisation_id', $organisation->id);
        })->get();

        if (!$enrolledUsers) {
            abort(404);
        }

        $students = [];

        foreach ($enrolledUsers as $eu) {
            $userScores = $eu->lessons()
                ->with('lesson')
                ->whereHas('lesson', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->select('user_lessons.score') // Select specific columns
                ->get();

            // dd(array_map(fn ($item) => $item->score, $userScores));

            $students[] = [
                'user' => $eu->only(['id', 'name', 'email']),
                'score' => $userScores->sum('score'),
                // 'score' => array_sum(array_map(fn($item)=>$item->score, $userScores)), // Array of lesson scores for the user
                'scores' => $userScores, // Array of lesson scores for the user
            ];
        }

        $students = collect($students);
        $sorted = $students->sortByDesc('score');


        return Inertia::render('Organisation/Course/Leaderboard/Index', [
            "course" => $course,
            "students" => $sorted->values()->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        // TODO: if course is public
        if (!$course->is_published) {
            abort(401);
        }

        if (!$course->enrolledUsers->contains($user)) {
            $course->enrolledUsers()->attach($user);

            // return response()->json(['message' => 'User successfully enrolled in the course!'], 201);
            return redirect()->back()->with(['global:message' => [
                'status' => 'success',
                'message' => 'User successfully enrolled in the course!',
            ]], 201);
            //return redirect(route('classrom'))
        } else {
            //return redirect(route('classrom'))
            // return response()->json(['message' => 'User is already enrolled in this course.'], 422);
        }
    }

    public function storeAll(Request $request, Course $course)
    {
        $user = $request->user();
        $org = $user->organisationNew->organisation;

        // TODO: if course is public
        if (!$course->is_published || !$user->isAdminInOrganisation($org)) {
            abort(401);
        }

        $request->validate(['students' => 'array']);


        $studentIds = $request->input('students', []);

        // $course->enrolledUsers()->createMany()

        $studentArr = array();

        foreach ($studentIds as $key => $ids) {
            $studentArr[] = ['user_id' => $ids, 'course_id' => $course->id];
        }

        // dd($studentArr);

        CourseEnrollment::upsert($studentArr, uniqueBy: ['user_id', 'course_id'], update: []);

        return redirect()->back()->with(['message' => [
            'status' => 'success',
            'message' => 'Students have been enrolled',
        ]], 200);
    }

    public function storeGroups(Request $request, Course $course)
    {
        $user = $request->user();
        $org = $user->organisationNew->organisation;

        // TODO: if course is public
        if (!$course->is_published || !$user->isAdminInOrganisation($org)) {
            abort(401);
        }

        $request->validate(['groups' => 'array']);

        $groupIds = $request->input('groups', []);
        
        // Get all users from the selected groups
        $allUserIds = [];
        foreach ($groupIds as $groupId) {
            $group = Group::where('id', $groupId)
                ->where('organisation_id', $org->id)
                ->with('users')
                ->first();
            
            if ($group) {
                $userIds = $group->users->pluck('id')->toArray();
                $allUserIds = array_merge($allUserIds, $userIds);
            }
        }

        // Remove duplicates
        $allUserIds = array_unique($allUserIds);

        // Create enrollment records
        $enrollmentArr = [];
        foreach ($allUserIds as $userId) {
            $enrollmentArr[] = ['user_id' => $userId, 'course_id' => $course->id];
        }

        CourseEnrollment::upsert($enrollmentArr, uniqueBy: ['user_id', 'course_id'], update: []);

        return redirect()->back()->with(['message' => [
            'status' => 'success',
            'message' => 'Groups have been enrolled successfully',
        ]], 200);
    }


    public function reset(Request $request, Course $course, User $student)
    {

        $user = $request->user();
        $org = $user->organisationNew->organisation;

        if (!$user->isAdminInOrganisation($org) || !$student->isMemberOfOrganisation($org)) {
            return abort(401);
            // return redirect()->back()->with(['global:message' => [
            //     'status' => 'error',
            //     'message' => 'You dont have permission to access this resource!',
            // ]], 401);
        }


        // Update course progress
        $enrollment = CourseEnrollment::where('user_id', $student->id)
            ->where('course_id', $course->id)->update(['is_completed' => 0]);

        // Update lesson progress
        $student->lessons()->with('lesson')->whereHas('lesson', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->update(['completed' => 0, 'score' => null, 'answers' => null]);


        return redirect()->back()->with(['message' => [
            'status' => 'success',
            'message' => 'User progress has been reset',
        ]], 200);
    }




    public function resetAll(Request $request, Course $course)
    {

        $user = $request->user();
        $org = $user->organisationNew->organisation;

        if (!$user->isAdminInOrganisation($org)) {
            return abort(401);
            // return redirect()->back()->with(['global:message' => [
            //     'status' => 'error',
            //     'message' => 'You dont have permission to access this resource!',
            // ]], 401);
        }

        // validate;
        $request->validate(['students' => 'array']);


        $studentIds = $request->input('students', []);

        $enrollments = CourseEnrollment::where('course_id', $course->id)->whereIn('user_id', $studentIds)->update([
            'is_completed' => 0
        ]);

        // Update lesson progress
        $studentLessons = UserLesson::whereHas('lesson', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->whereIn('user_id', $studentIds)->update([
            'score' => null,
            'answers' => null,
            'completed' => 0,
        ]);;
        // dd($studentLessons);


        return redirect()->back()->with(['global:message' => [
            'status' => 'success',
            'message' => 'Student progress has been reset',
        ]], 200);
    }


    public function destroy(Request $request, Course $course, User $student)
    {

        $user = $request->user();
        $org = $user->organisationNew->organisation;

        if (!$user->isAdminInOrganisation($org) || !$student->isMemberOfOrganisation($org)) {
            return abort(401);
            // return redirect()->back()->with(['global:message' => [
            //     'status' => 'error',
            //     'message' => 'You dont have permission to access this resource!',
            // ]], 401);
        }


        // Update course progress
        $enrollment = CourseEnrollment::where('user_id', $student->id)
            ->where('course_id', $course->id)->delete();

        // Update lesson progress
        $student->lessons()->with('lesson')->whereHas('lesson', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->delete();


        return redirect()->back()->with(['message' => [
            'status' => 'success',
            'message' => 'User enrollment has been deleted',
        ]], 200);
    }
}
