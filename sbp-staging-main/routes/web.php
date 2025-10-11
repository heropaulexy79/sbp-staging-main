<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\OrganisationUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CourseController as PublicCourseController;
use App\Http\Controllers\RagController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UploadController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);

    return view('welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/terms-and-conditions', function () {
    return view('terms-conditions', []);
})->name('website.terms');
Route::get('/privacy-policy', function () {
    return view('privacy-policy', []);
})->name('website.privacy');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'subscribed'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Uploads
    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    Route::delete('/upload', [UploadController::class, 'destroy'])->name('upload.delete');

    // RAG AI Content Generation
    Route::middleware(['subscribed'])->group(function () {
        Route::post('/api/rag/generate-content', [RagController::class, 'generateContent'])->name('rag.generate');
        Route::post('/api/rag/add-knowledge', [RagController::class, 'addKnowledge'])->name('rag.add-knowledge');
        Route::get('/api/rag/search', [RagController::class, 'searchKnowledge'])->name('rag.search');
        Route::post('/api/rag/log-generation', [RagController::class, 'logLessonGeneration'])->name('rag.log-generation');
        Route::get('/api/rag/test', [RagController::class, 'testConnection'])->name('rag.test');
        Route::post('/api/rag/sync', [RagController::class, 'syncKnowledgeBase'])->name('rag.sync');
    });

    // Resource Management (Vector Database)
    Route::middleware(['subscribed'])->group(function () {
        Route::post('/api/resources/upload', [ResourceController::class, 'uploadResource'])->name('resources.upload');
        Route::get('/api/resources/course/{courseId}', [ResourceController::class, 'getCourseResources'])->name('resources.course');
        Route::post('/api/resources/search', [ResourceController::class, 'searchResources'])->name('resources.search');
    });

    // Quiz Management System
    Route::middleware(['subscribed'])->group(function () {
        // Quiz Generation & Management (Admin/Teacher)
        Route::post('/api/quizzes/generate', [QuizController::class, 'generateQuizzes'])->name('quizzes.generate');
        Route::get('/api/quizzes/types', [QuizController::class, 'getQuizTypes'])->name('quizzes.types');
        Route::get('/api/quizzes/check-content/{courseId}', [QuizController::class, 'checkCourseContent'])->name('quizzes.check-content');
        Route::get('/api/quizzes/stats/{courseId}', [QuizController::class, 'getQuizStats'])->name('quizzes.stats');
        Route::get('/api/quizzes/generated/{courseId}', [QuizController::class, 'getGeneratedQuizzes'])->name('quizzes.generated');
        Route::post('/api/quizzes/assign', [QuizController::class, 'assignQuizzesToLesson'])->name('quizzes.assign');
        Route::get('/api/quizzes/lesson/{lessonId}', [QuizController::class, 'getLessonQuizzes'])->name('quizzes.lesson');
        Route::delete('/api/quizzes/question/{questionId}', [QuizController::class, 'deleteQuizQuestion'])->name('quizzes.question.delete');

        // Quiz Taking (Students)
        Route::post('/api/quizzes/attempt/{lessonId}/start', [QuizController::class, 'startQuizAttempt'])->name('quizzes.attempt.start');
        Route::post('/api/quizzes/attempt/{attemptId}/answer', [QuizController::class, 'submitAnswer'])->name('quizzes.attempt.answer');
        Route::post('/api/quizzes/attempt/{attemptId}/complete', [QuizController::class, 'completeQuizAttempt'])->name('quizzes.attempt.complete');
        Route::get('/api/quizzes/attempt/{attemptId}/results', [QuizController::class, 'getQuizResults'])->name('quizzes.attempt.results');
        Route::get('/api/quizzes/lesson/{lessonId}/attempts', [QuizController::class, 'getUserQuizAttempts'])->name('quizzes.lesson.attempts');
    });

    // Organisation
    Route::resource('organisation', OrganisationController::class)->only(['store', 'update']);
    Route::get('/settings/org', [OrganisationController::class, 'edit'])->name('organisation.edit');

    Route::delete('/org/{organisation}/invite/{invitation}', [OrganisationController::class, 'uninviteEmployee'])->name('organisation.uninvite')->middleware(['subscribed']);

    Route::post('/org/{organisation}/invite', [OrganisationUserController::class, 'store'])->name('organisation.invite')->middleware(['subscribed']);
    Route::patch('/org/{organisation}/employee/{employee}', [OrganisationUserController::class, 'update'])->name('organisation.updateEmployee')->middleware(['subscribed']);
    Route::delete('/org/{organisation}/employee/{employee}', [OrganisationUserController::class, 'destroy'])->name('organisation.employee.delete')->middleware(['subscribed']);
    Route::get('/org/employee/', [OrganisationUserController::class, 'index'])->name('organisation.employees')->middleware(['subscribed']);

    Route::get('/settings/subscription', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/settings/subscription', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::delete('/settings/subscription', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::get('/settings/billing', [SubscriptionController::class, 'index'])->name('organisation.billing.index');

    // Org-course
    Route::get('/org/course', [CourseController::class, 'index'])->name('course.index')->middleware(['subscribed']);
    // Route::get("/org/{organisation}/course", [CourseController::class, 'index'])->name('course.index');
    Route::get('/org/course/{course}', [CourseController::class, 'show'])->name('course.show')->middleware(['subscribed']);
    Route::get('/org/course/{course}/edit', [CourseController::class, 'edit'])->name('course.edit')->middleware(['subscribed']);
    Route::get('/org/course/create', [CourseController::class, 'create'])->name('course.create')->middleware(['subscribed']);

    Route::post('/org/course', [CourseController::class, 'store'])->name('course.store')->middleware(['subscribed']);
    Route::patch('/org/course/{course}', [CourseController::class, 'update'])->name('course.update')->middleware(['subscribed']);
    Route::delete('/org/course/{course}', [CourseController::class, 'destroy'])->name('course.destroy')->middleware(['subscribed']);

    Route::get('/org/course/{course:slug}/stats/leaderboard', [CourseEnrollmentController::class, 'show'])->name('organisation.course.leaderboard')->middleware(['subscribed']);
    Route::delete('/org/course/{course:slug}/student/{student}', [CourseEnrollmentController::class, 'destroy'])->name('organisation.course.student.destroy')->middleware(['subscribed']);
    Route::delete('/org/course/{course:slug}/stats/leaderboard/reset', [CourseEnrollmentController::class, 'resetAll'])->name('organisation.course.leaderboard.reset.students.progress')->middleware(['subscribed']);
    Route::delete('/org/course/{course:slug}/stats/leaderboard/reset/{student}', [CourseEnrollmentController::class, 'reset'])->name('organisation.course.leaderboard.reset.student.progress')->middleware(['subscribed']);
    Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'storeAll'])->name('course.enroll')->middleware(['subscribed']);
    Route::post('/course/{course:slug}/enroll/groups', [CourseEnrollmentController::class, 'storeGroups'])->name('course.enroll.groups')->middleware(['subscribed']);

    // Org-groups
    Route::get('/org/groups', [GroupController::class, 'index'])->name('group.index')->middleware(['subscribed']);
    Route::get('/org/groups/api', [GroupController::class, 'getAllGroups'])->name('group.api')->middleware(['subscribed']);
    Route::get('/org/groups/{group}', [GroupController::class, 'show'])->name('group.show')->middleware(['subscribed']);
    Route::get('/org/groups/{group}/edit', [GroupController::class, 'edit'])->name('group.edit')->middleware(['subscribed']);
    Route::get('/org/groups/create', [GroupController::class, 'create'])->name('group.create')->middleware(['subscribed']);

    Route::post('/org/groups', [GroupController::class, 'store'])->name('group.store')->middleware(['subscribed']);
    Route::patch('/org/groups/{group}', [GroupController::class, 'update'])->name('group.update')->middleware(['subscribed']);
    Route::delete('/org/groups/{group}', [GroupController::class, 'destroy'])->name('group.destroy')->middleware(['subscribed']);

    // Course - public?
    Route::get('/course', [PublicCourseController::class, 'index'])->name('public.course.index')->middleware(['subscribed']);
    Route::get('/course/{course:slug}', [PublicCourseController::class, 'show'])->name('public.course.show')->middleware(['subscribed']);
    // Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'store'])->name('course.enroll');

    // Lesson - public?
    Route::get('/course/{course}/lesson/{lesson}', [LessonController::class, 'show'])->name('lesson.show')->middleware(['subscribed']);
    // Lesson
    Route::get('/org/course/{course}/lesson/{lesson}/edit', [LessonController::class, 'edit'])->name('lesson.edit')->middleware(['subscribed']);
    Route::patch('/org/course/{course}/lesson/{lesson}', [LessonController::class, 'update'])->name('lesson.update')->middleware(['subscribed']);
    Route::patch('/org/course/{course}/lesson/{lesson}/postion', [LessonController::class, 'updatePosition'])->name('lesson.update.position')->middleware(['subscribed']);
    Route::get('/org/course/{course}/lesson/create', [LessonController::class, 'create'])->name('lesson.create')->middleware(['subscribed']);
    Route::post('/org/course/{course}/lesson', [LessonController::class, 'store'])->name('lesson.store')->middleware(['subscribed']);

    // Classrooom
    Route::middleware(['subscribed', 'enrolled'])->group(function () {
        // TODO: FIX show:slug
        Route::get('/classroom/course/{course:slug}', [ClassroomController::class, 'showCourse'])->name('classroom.course.show');
        Route::get('/classroom/course/{course:slug}/completed', [ClassroomController::class, 'showCompleted'])->name('classroom.course.completed.show');
        Route::get('/classroom/course/{course:slug}/lesson', [ClassroomController::class, 'showLessons'])->name('classroom.lesson.index');
        Route::get('/classroom/course/{course:slug}/lesson/{lesson:slug}', [ClassroomController::class, 'showLesson'])->name('classroom.lesson.show');
        Route::patch('/classroom/course/{course:slug}/lesson/{lesson:slug}/mark-complete', [ClassroomController::class, 'markLessonComplete'])->name('classroom.lesson.markComplete');
        Route::patch('/classroom/course/{course:slug}/lesson/{lesson:slug}/answer-quiz', [ClassroomController::class, 'answerQuiz'])->name('classroom.lesson.answerQuiz');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/payment/paystack/pay', [App\Http\Controllers\Payments\PaystackController::class, 'redirectToGateway'])->name('paystack.pay');
    Route::get('/payment/paystack/callback', [App\Http\Controllers\Payments\PaystackController::class, 'handleGatewayCallback'])->name('paystack.pay.gateway');
    Route::get('/payment/paystack/wbhk', [App\Http\Controllers\Payments\PaystackController::class, 'handleWebhook'])->name('paystack.pay.webhook');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/preview/welcome', function () {
    $mail = new \App\Mail\WelcomeMail(\App\Models\User::first());
    return $mail->render();
});


require __DIR__ . '/auth.php';
