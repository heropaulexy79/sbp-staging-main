<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Services\QuizGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class QuizController extends Controller
{
    private QuizGeneratorService $quizGeneratorService;

    public function __construct(QuizGeneratorService $quizGeneratorService)
    {
        $this->quizGeneratorService = $quizGeneratorService;
    }

    /**
     * Generate quizzes for a course.
     */
    public function generateQuizzes(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|integer|exists:courses,id',
                'quiz_title' => 'required|string|max:255',
                'quiz_types' => 'required|array|min:1',
                'quiz_types.*' => 'in:MULTIPLE_CHOICE,MULTIPLE_SELECT,TRUE_FALSE,TYPE_ANSWER,PUZZLE',
                'quiz_count' => 'required|integer|min:1|max:50',
                'difficulty' => 'nullable|in:easy,medium,hard',
                'is_published' => 'nullable|boolean',
                'reference_resources' => 'nullable|array',
                'reference_resources.*' => 'uuid|exists:resources,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user has access to the course
            $course = Course::findOrFail($request->course_id);
            if (!$this->canAccessCourse($course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to generate quizzes for this course'
                ], 403);
            }

            // Create a new quiz lesson first
            $lesson = Lesson::create([
                'course_id' => $request->course_id,
                'title' => $request->quiz_title,
                'slug' => \Illuminate\Support\Str::slug($request->quiz_title),
                'content' => 'AI-generated quiz lesson',
                'type' => 'QUIZ',
                'position' => Lesson::where('course_id', $request->course_id)->max('position') + 1,
                'is_published' => $request->input('is_published', false)
            ]);

            $options = [
                'difficulty' => $request->difficulty,
                'reference_resources' => $request->reference_resources ?? []
            ];

            $result = $this->quizGeneratorService->generateQuizzesForLesson(
                $lesson->id,
                $request->course_id,
                $request->quiz_types,
                $request->quiz_count,
                $options
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully created quiz lesson '{$lesson->title}' with {$result['generated_count']} questions",
                'data' => [
                    'lesson' => $lesson,
                    'quizzes' => $result['quizzes'],
                    'generated_count' => $result['generated_count']
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Quiz generation failed in controller', [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available quiz types.
     */
    public function getQuizTypes(): JsonResponse
    {
        try {
            $types = $this->quizGeneratorService->getAvailableQuizTypes();
            
            return response()->json([
                'success' => true,
                'data' => $types
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz types'
            ], 500);
        }
    }

    /**
     * Check if a course has content available for quiz generation.
     */
    public function checkCourseContent(Request $request, int $courseId): JsonResponse
    {
        try {
            $course = Course::findOrFail($courseId);
            if (!$this->canAccessCourse($course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to check content for this course'
                ], 403);
            }

            $contentInfo = $this->quizGeneratorService->checkCourseContent($courseId);
            
            return response()->json([
                'success' => true,
                'data' => $contentInfo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check course content'
            ], 500);
        }
    }

    /**
     * Get quiz statistics for a course.
     */
    public function getQuizStats(Request $request, int $courseId): JsonResponse
    {
        try {
            $course = Course::findOrFail($courseId);
            if (!$this->canAccessCourse($course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view quiz statistics for this course'
                ], 403);
            }

            $stats = $this->quizGeneratorService->getQuizStats($courseId);
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz statistics'
            ], 500);
        }
    }

    /**
     * Get generated quizzes for a course (not yet assigned to lessons).
     */
    public function getGeneratedQuizzes(Request $request, int $courseId): JsonResponse
    {
        try {
            $course = Course::findOrFail($courseId);
            if (!$this->canAccessCourse($course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view quizzes for this course'
                ], 403);
            }

            $quizzes = QuizQuestion::whereNull('lesson_id')
                ->whereJsonContains('metadata->course_id', $courseId)
                ->with('options')
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $quizzes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch generated quizzes'
            ], 500);
        }
    }

    /**
     * Assign quizzes to a lesson.
     */
    public function assignQuizzesToLesson(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'question_ids' => 'required|array|min:1',
                'question_ids.*' => 'integer|exists:quiz_questions,id',
                'lesson_id' => 'required|integer|exists:lessons,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lesson = Lesson::findOrFail($request->lesson_id);
            if (!$this->canAccessCourse($lesson->course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to assign quizzes to this lesson'
                ], 403);
            }

            $assignedQuestions = $this->quizGeneratorService->assignQuizzesToLesson(
                $request->question_ids,
                $request->lesson_id
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned " . count($assignedQuestions) . " questions to lesson",
                'data' => $assignedQuestions
            ]);

        } catch (Exception $e) {
            Log::error('Quiz assignment failed in controller', [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quizzes for a specific lesson.
     */
    public function getLessonQuizzes(Request $request, int $lessonId): JsonResponse
    {
        try {
            $lesson = Lesson::findOrFail($lessonId);
            if (!$this->canAccessCourse($lesson->course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view quizzes for this lesson'
                ], 403);
            }

            $quizzes = QuizQuestion::where('lesson_id', $lessonId)
                ->with('options')
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $quizzes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lesson quizzes'
            ], 500);
        }
    }

    /**
     * Start a quiz attempt.
     */
    public function startQuizAttempt(Request $request, int $lessonId): JsonResponse
    {
        try {
            $lesson = Lesson::findOrFail($lessonId);
            if (!$this->canAccessCourse($lesson->course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to take quizzes for this lesson'
                ], 403);
            }

            // Check if user is enrolled in the course
            if (!Auth::user()->isEnrolledInCourse($lesson->course_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be enrolled in this course to take quizzes'
                ], 403);
            }

            // Check if there's already an in-progress attempt
            $existingAttempt = QuizAttempt::where('lesson_id', $lessonId)
                ->where('user_id', Auth::id())
                ->where('status', QuizAttempt::STATUS_IN_PROGRESS)
                ->first();

            if ($existingAttempt) {
                return response()->json([
                    'success' => true,
                    'message' => 'Resuming existing quiz attempt',
                    'data' => $existingAttempt->load('answers')
                ]);
            }

            // Create new attempt
            $attempt = QuizAttempt::create([
                'lesson_id' => $lessonId,
                'user_id' => Auth::id(),
                'status' => QuizAttempt::STATUS_IN_PROGRESS
            ]);

            // Get quiz questions (without correct answers for students)
            $questions = QuizQuestion::where('lesson_id', $lessonId)
                ->with(['options' => function ($query) {
                    $query->select('id', 'quiz_question_id', 'option_text', 'position')
                          ->orderBy('position');
                }])
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Quiz attempt started',
                'data' => [
                    'attempt' => $attempt,
                    'questions' => $questions
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Quiz attempt start failed', [
                'user_id' => Auth::id(),
                'lesson_id' => $lessonId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz attempt'
            ], 500);
        }
    }

    /**
     * Submit an answer for a quiz question.
     */
    public function submitAnswer(Request $request, int $attemptId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'question_id' => 'required|integer|exists:quiz_questions,id',
                'answer' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $attempt = QuizAttempt::findOrFail($attemptId);
            
            // Verify ownership
            if ($attempt->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to submit answers for this attempt'
                ], 403);
            }

            // Check if attempt is still in progress
            if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
                return response()->json([
                    'success' => false,
                    'message' => 'This quiz attempt has already been completed'
                ], 400);
            }

            $question = QuizQuestion::findOrFail($request->question_id);
            
            // Check if question belongs to the same lesson
            if ($question->lesson_id !== $attempt->lesson_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question does not belong to this quiz attempt'
                ], 400);
            }

            // Check if answer already exists
            $existingAnswer = QuizAnswer::where('quiz_attempt_id', $attemptId)
                ->where('quiz_question_id', $request->question_id)
                ->first();

            if ($existingAnswer) {
                // Update existing answer
                $isCorrect = $question->isAnswerCorrect($request->answer);
                $existingAnswer->update([
                    'answer' => $request->answer,
                    'is_correct' => $isCorrect
                ]);
                $answer = $existingAnswer;
            } else {
                // Create new answer
                $isCorrect = $question->isAnswerCorrect($request->answer);
                $answer = QuizAnswer::create([
                    'quiz_attempt_id' => $attemptId,
                    'quiz_question_id' => $request->question_id,
                    'answer' => $request->answer,
                    'is_correct' => $isCorrect
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
                'data' => $answer
            ]);

        } catch (Exception $e) {
            Log::error('Answer submission failed', [
                'user_id' => Auth::id(),
                'attempt_id' => $attemptId,
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit answer'
            ], 500);
        }
    }

    /**
     * Complete a quiz attempt.
     */
    public function completeQuizAttempt(Request $request, int $attemptId): JsonResponse
    {
        try {
            $attempt = QuizAttempt::findOrFail($attemptId);
            
            // Verify ownership
            if ($attempt->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to complete this attempt'
                ], 403);
            }

            // Check if attempt is still in progress
            if ($attempt->status !== QuizAttempt::STATUS_IN_PROGRESS) {
                return response()->json([
                    'success' => false,
                    'message' => 'This quiz attempt has already been completed'
                ], 400);
            }

            // Complete the attempt
            $attempt->complete();

            return response()->json([
                'success' => true,
                'message' => 'Quiz completed successfully',
                'data' => [
                    'attempt' => $attempt->fresh(),
                    'score' => $attempt->score,
                    'total' => $attempt->total,
                    'percentage' => $attempt->getPercentageScore(),
                    'grade' => $attempt->getGrade(),
                    'passed' => $attempt->isPassed()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Quiz completion failed', [
                'user_id' => Auth::id(),
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete quiz'
            ], 500);
        }
    }

    /**
     * Get quiz attempt results.
     */
    public function getQuizResults(Request $request, int $attemptId): JsonResponse
    {
        try {
            $attempt = QuizAttempt::with(['answers.question.options', 'lesson'])
                ->findOrFail($attemptId);
            
            // Verify ownership or admin access
            if ($attempt->user_id !== Auth::id() && !$this->canAccessCourse($attempt->lesson->course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view these results'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt' => $attempt,
                    'score' => $attempt->score,
                    'total' => $attempt->total,
                    'percentage' => $attempt->getPercentageScore(),
                    'grade' => $attempt->getGrade(),
                    'passed' => $attempt->isPassed(),
                    'answers' => $attempt->answers
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz results'
            ], 500);
        }
    }

    /**
     * Get user's quiz attempts for a lesson.
     */
    public function getUserQuizAttempts(Request $request, int $lessonId): JsonResponse
    {
        try {
            $lesson = Lesson::findOrFail($lessonId);
            if (!$this->canAccessCourse($lesson->course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view quiz attempts for this lesson'
                ], 403);
            }

            $attempts = QuizAttempt::where('lesson_id', $lessonId)
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $attempts
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz attempts'
            ], 500);
        }
    }

    /**
     * Delete a quiz question.
     */
    public function deleteQuizQuestion(Request $request, int $questionId): JsonResponse
    {
        try {
            $question = QuizQuestion::with('lesson.course')->findOrFail($questionId);
            
            if (!$this->canAccessCourse($question->lesson->course)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this quiz question'
                ], 403);
            }

            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Quiz question deleted successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz question'
            ], 500);
        }
    }

    /**
     * Check if user can access a course.
     */
    private function canAccessCourse(Course $course): bool
    {
        $user = Auth::user();
        
        // Admin can access any course in their organization
        if ($user->role === 'ADMIN' && $user->organisation_id === $course->organisation_id) {
            return true;
        }
        
        // Teacher can access courses they created
        if ($course->teacher_id === $user->id) {
            return true;
        }
        
        // Students can access courses they're enrolled in
        if ($user->isEnrolledInCourse($course->id)) {
            return true;
        }
        
        return false;
    }
}
