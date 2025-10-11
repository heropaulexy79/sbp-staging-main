<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    // Only allow if user is enrolled

    public function showCourse(Request $request, Course $course)
    {
        return redirect(route('classroom.lesson.index', ['course' => $course->slug]));
    }

    public function showLessons(Request $request, Course $course)
    {

        $user = $request->user();

        $lastCompletedLesson = UserLesson::where('user_id', $user->id)
            ->where('completed', 1)
            ->join('lessons', 'user_lessons.lesson_id', '=', 'lessons.id')
            ->select('user_lessons.*', 'lessons.course_id', 'lessons.is_published', 'lessons.position')
            ->where('lessons.is_published', 1)
            ->where('lessons.course_id', $course->id)
            ->orderBy('lessons.position', 'desc')
            ->first();


        $redirectLesson = $lastCompletedLesson->id ?? $course->lessons()->first(); // Redirect to 1st if none completed

        return redirect(route('classroom.lesson.show', ['course' => $course->slug, 'lesson' => $redirectLesson->slug]));
    }


    public function showLesson(Request $request, Course $course, Lesson $lesson)
    {

        $user = $request->user();

        $temp_content_json = $lesson->content_json;

        if ($lesson->type === Lesson::TYPE_QUIZ) {
            // Prioritize AI-generated quizzes from quiz_questions table
            if ($lesson->hasAiGeneratedQuestions()) {
                $aiGeneratedQuestions = $lesson->quizQuestions()->with('options')->get();
                // Convert AI-generated questions to the format expected by frontend
                $lesson->content_json = $this->formatAiGeneratedQuestions($aiGeneratedQuestions);
            } else {
                // Fallback to old content_json format
                $lesson->content_json = $lesson->quizWithoutCorrectAnswer();
            }
        }

        $lessons = $course->lessons()->published()->orderBy('position')
            ->with(['user_lesson' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get(['title', 'position', 'type', 'id', 'slug',]);
        $total_completed = 0;

        // foreach ($lessons as $l) {
        //     $l->completed = UserLesson::where('user_id', $user->id)
        //         ->where('lesson_id', $l->id)
        //         ->where('completed', 1)->exists() ?? false;

        //     if ($l->completed) {
        //         $total_completed++;
        //     }
        // };
        foreach ($lessons as $l) {
            $l->completed = $l->user_lesson->first()?->completed === 1;


            if ($l->completed) {
                $total_completed++;
            }
        };




        // dd($lessons);

        if ($total_completed === count($lessons)) {
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment) {
                $enrollment->is_completed = true;
                $enrollment->save();
            }
        }

        $user_lesson = $lessons->filter(function ($item) use ($lesson) {
            return $item['id'] === $lesson->id;  // Strict comparison with ===
        })->first()->user_lesson->first();


        $lesson->completed = $user_lesson?->completed === 1;
        $lesson->answers = $user_lesson?->answers ?? null;

        // For completed lessons, only restore legacy JSON for non-AI quizzes.
        // AI-generated quizzes should keep the formatted quizQuestions JSON we built above.
        if ($lesson->completed && !$lesson->hasAiGeneratedQuestions()) {
            $lesson->content_json = $temp_content_json;
        }

        $lessons->makeHidden('user_lesson');

        return Inertia::render('Classroom/Lesson', [
            'course' => $course,
            'lessons' => $lessons,
            'lesson' => $lesson,
        ]);
    }

    public function showCompleted(Request $request, Course $course)
    {

        $user = $request->user();
        $lessons = $course->lessons()->published()->orderBy('position')
            ->with(['user_lesson' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get(['title', 'position', 'type', 'id', 'slug',]);
        $total_completed = 0;

        foreach ($lessons as $l) {
            $l->completed = $l->user_lesson->first()?->completed === 1;
            if ($l->completed) {
                $total_completed++;
            }
        };

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        if ($total_completed === count($lessons)) {

            if ($enrollment) {
                $enrollment->is_completed = true;
                $enrollment->save();
            }
        }


        $lessons->makeHidden('user_lesson');


        // if (!$enrollment->is_completed) {
        //     return redirect(route('classroom.lesson.index', ['course' => $course->slug]));
        // }

        // dd($enrollment);
        $userScores = $user->lessons()
            ->with('lesson')
            ->whereHas('lesson', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->select('user_lessons.score') // Select specific columns
            ->get();




        return Inertia::render('Classroom/Completed', [
            'course' => $course,
            'lessons' => $lessons,
            'enrollment' => $enrollment,
            'progress' => ($total_completed / count($lessons) * 100),
            'completed_lessons' => $total_completed,
            'total_score' => $userScores->sum('score'),
        ]);
    }



    public function markLessonComplete(Request $request, Course $course, Lesson $lesson)
    {

        $user = $request->user();

        // $user_lesson = UserLesson::where('user_id', $user->id)
        // ->where('lesson_id', $lessonId)
        // ->first();

        UserLesson::upsert(
            [['user_id' => $user->id, 'lesson_id' => $lesson->id, 'completed' => true],],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['completed']
        );

        // TODO: MOve complete enrollment here?

        $next_lesson_id = $request->query('next') ?? $lesson->slug;

        return redirect(route('classroom.lesson.show', ['course' => $course->slug, 'lesson' => $next_lesson_id]));
    }


    public function answerQuiz(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();

        $request->validate([
            'answers' => 'required|array|min:0',
            'answers.*.question_id' => 'required',
            // selected_option can be string (single/true_false/type_answer) or array (multiple_select)
            'answers.*.selected_option' => 'nullable',
        ]);

        $score = 0.0;
        $total = 0.0;

        // Check if this is an AI-generated quiz
        if ($lesson->hasAiGeneratedQuestions()) {
            $aiGeneratedQuestions = $lesson->quizQuestions()->with('options')->get();
            // Handle AI-generated quiz scoring
            $answers = $request->input('answers');
            
            foreach ($answers as $answer) {
                $question = $aiGeneratedQuestions->find($answer['question_id']);
                if (!$question) continue;
                
                $total++;
                
                // Check if answer is correct based on question type
                if ($this->isAnswerCorrect($question, $answer['selected_option'])) {
                    $score++;
                }
            }
        } else {
            // Fallback to old quiz scoring system
            $quiz = $lesson->content_json;
            $answers = $request->input('answers');

            foreach ($answers as $key => $value) {
                $v = array_search($value['question_id'],  array_column($quiz, 'id'));
                if ($v === false) {
                    continue;
                }
                $question = $quiz[$v];

                if (!$question) {
                    continue;
                }

                if ($question['type'] === 'single_choice') {
                    $total++;

                    if ($question['correct_option'] === $value['selected_option']) {
                        $score++;
                    }
                }
            }
        }


        $scoreInPercent = ($score / $total) * 100;

        // Todo : Quiz, lesson
        UserLesson::upsert(
            [[
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'completed' => true,
                'answers' => json_encode($answers),
                'score' => $scoreInPercent
            ]],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['completed', 'score', 'answers']
        );

        return redirect()->back()->with('message', [
            'status' => 'success',
            'message' => 'You scored ' . $score . ' out of ' . $total,
            'score' => $scoreInPercent
        ]);
    }

    /**
     * Format AI-generated quiz questions to match the expected frontend format
     */
    private function formatAiGeneratedQuestions($questions)
    {
        return $questions->map(function ($question) {
            $formatted = [
                'id' => (string) $question->id,
                'text' => $question->question,
                'type' => $this->mapQuestionType($question->type),
                'options' => []
            ];

            // Add options for questions that have them
            if ($question->options->isNotEmpty()) {
                foreach ($question->options as $option) {
                    $formatted['options'][] = [
                        'id' => (string) $option->id,
                        'text' => $option->option_text,
                        'is_correct' => $option->is_correct
                    ];
                }
            }

            // Add correct_option for compatibility with existing frontend
            if ($question->options->isNotEmpty()) {
                $correctOption = $question->options->where('is_correct', true)->first();
                if ($correctOption) {
                    $formatted['correct_option'] = (string) $correctOption->id;
                }
            }

            return $formatted;
        })->toArray();
    }

    /**
     * Map AI-generated question types to frontend expected types
     */
    private function mapQuestionType($aiType)
    {
        $typeMap = [
            'MULTIPLE_CHOICE' => 'single_choice',
            'MULTIPLE_SELECT' => 'multiple_select',
            'TRUE_FALSE' => 'true_false',
            'TYPE_ANSWER' => 'type_answer',
            'PUZZLE' => 'puzzle'
        ];

        return $typeMap[$aiType] ?? 'single_choice';
    }

    /**
     * Check if an answer is correct for AI-generated questions
     */
    private function isAnswerCorrect($question, $selectedOption)
    {
        switch ($question->type) {
            case 'MULTIPLE_CHOICE':
            case 'TRUE_FALSE':
                // For single choice, check if selected option is correct
                $correctOption = $question->options->where('is_correct', true)->first();
                return $correctOption && $correctOption->id == $selectedOption;
                
            case 'MULTIPLE_SELECT':
                // Ensure arrays
                $selected = is_array($selectedOption) ? $selectedOption : [$selectedOption];
                $selected = array_filter($selected, fn($v) => !is_null($v) && $v !== '');

                $correctIds = $question->options->where('is_correct', true)->pluck('id')->map(fn($id) => (string)$id)->values()->toArray();
                sort($correctIds);
                $selectedStr = array_map(fn($id) => (string)$id, $selected);
                sort($selectedStr);
                return $selectedStr === $correctIds;
                
            case 'TYPE_ANSWER':
            case 'PUZZLE':
                // For text-based answers, check against metadata
                $correctAnswer = $question->metadata['correct_answer'] ?? '';
                return strtolower(trim($selectedOption)) === strtolower(trim($correctAnswer));
                
            default:
                return false;
        }
    }
}
