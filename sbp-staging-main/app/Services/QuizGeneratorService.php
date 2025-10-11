<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class QuizGeneratorService
{
    private HybridRagService $ragService;

    public function __construct(HybridRagService $ragService)
    {
        $this->ragService = $ragService;
    }

    /**
     * Generate quizzes for a specific lesson.
     *
     * @param int $lessonId
     * @param int $courseId
     * @param array $quizTypes Array of quiz types to generate
     * @param int $quizCount Total number of questions to generate
     * @param array $options Additional options (difficulty, reference_resources, etc.)
     * @return array Generated quiz data
     * @throws Exception
     */
    public function generateQuizzesForLesson(int $lessonId, int $courseId, array $quizTypes, int $quizCount, array $options = []): array
    {
        try {
            // 1. Fetch course and lessons
            $course = Course::findOrFail($courseId);
            $lessons = $this->fetchCourseLessons($courseId);
            
            // 2. Prepare content for quiz generation
            $lessonContent = '';
            if ($lessons->isNotEmpty()) {
                $lessonContent = $this->prepareLessonContent($lessons);
            } else {
                // If no lessons, use course description and title
                $lessonContent = $this->prepareCourseContent($course);
            }
            
            // 3. Add reference resources if specified
            $referenceContent = [];
            if (!empty($options['reference_resources'])) {
                $referenceContent = $this->getReferenceResources($options['reference_resources']);
            }
            
            // 4. Ensure we have some content to work with
            if (empty(trim($lessonContent)) && empty($referenceContent)) {
                throw new Exception('No content available for quiz generation. Please add lessons or reference resources to the course.');
            }

            // 5. Generate quizzes using LLM
            $generatedQuizzes = $this->generateQuizzesWithLLM(
                $lessonContent,
                $referenceContent,
                $quizTypes,
                $quizCount,
                $options
            );

            // 6. Store quizzes in database with lesson_id
            $storedQuizzes = $this->storeGeneratedQuizzesForLesson($generatedQuizzes, $lessonId, $courseId);

            Log::info('Quiz generation completed for lesson', [
                'lesson_id' => $lessonId,
                'course_id' => $courseId,
                'quiz_count' => $quizCount,
                'generated_count' => count($storedQuizzes),
                'quiz_types' => $quizTypes
            ]);

            return [
                'success' => true,
                'lesson_id' => $lessonId,
                'course_id' => $courseId,
                'generated_count' => count($storedQuizzes),
                'quizzes' => $storedQuizzes
            ];

        } catch (Exception $e) {
            Log::error('Quiz generation failed for lesson', [
                'lesson_id' => $lessonId,
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception("Failed to generate quizzes: " . $e->getMessage());
        }
    }

    /**
     * Generate quizzes for a course based on lesson content.
     *
     * @param int $courseId
     * @param array $quizTypes Array of quiz types to generate
     * @param int $quizCount Total number of questions to generate
     * @param array $options Additional options (difficulty, reference_resources, etc.)
     * @return array Generated quiz data
     * @throws Exception
     */
    public function generateQuizzesForCourse(int $courseId, array $quizTypes, int $quizCount, array $options = []): array
    {
        try {
            // 1. Fetch course and lessons
            $course = Course::findOrFail($courseId);
            $lessons = $this->fetchCourseLessons($courseId);
            
            // 2. Prepare content for quiz generation
            $lessonContent = '';
            if ($lessons->isNotEmpty()) {
                $lessonContent = $this->prepareLessonContent($lessons);
            } else {
                // If no lessons, use course description and title
                $lessonContent = $this->prepareCourseContent($course);
            }
            
            // 3. Add reference resources if specified
            $referenceContent = [];
            if (!empty($options['reference_resources'])) {
                $referenceContent = $this->getReferenceResources($options['reference_resources']);
            }
            
            // 4. Ensure we have some content to work with
            if (empty(trim($lessonContent)) && empty($referenceContent)) {
                throw new Exception('No content available for quiz generation. Please add lessons or reference resources to the course.');
            }

            // 5. Generate quizzes using LLM
            $generatedQuizzes = $this->generateQuizzesWithLLM(
                $lessonContent,
                $referenceContent,
                $quizTypes,
                $quizCount,
                $options
            );

            // 6. Store quizzes in database
            $storedQuizzes = $this->storeGeneratedQuizzes($generatedQuizzes, $courseId);

            Log::info('Quiz generation completed', [
                'course_id' => $courseId,
                'quiz_count' => $quizCount,
                'generated_count' => count($storedQuizzes),
                'quiz_types' => $quizTypes
            ]);

            return [
                'success' => true,
                'course_id' => $courseId,
                'generated_count' => count($storedQuizzes),
                'quizzes' => $storedQuizzes
            ];

        } catch (Exception $e) {
            Log::error('Quiz generation failed', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception("Failed to generate quizzes: " . $e->getMessage());
        }
    }

    /**
     * Fetch all lessons for a course.
     */
    private function fetchCourseLessons(int $courseId)
    {
        // First try to get published lessons
        $lessons = Lesson::where('course_id', $courseId)
            ->where('is_published', true)
            ->orderBy('position')
            ->get();
            
        // If no published lessons, get all lessons (including drafts)
        if ($lessons->isEmpty()) {
            $lessons = Lesson::where('course_id', $courseId)
                ->orderBy('position')
                ->get();
        }
        
        return $lessons;
    }

    /**
     * Prepare lesson content for LLM processing.
     */
    private function prepareLessonContent($lessons): string
    {
        $content = "Course Lessons Content:\n\n";
        
        foreach ($lessons as $index => $lesson) {
            $content .= "=== Lesson " . ($index + 1) . ": {$lesson->title} ===\n";
            $content .= "Content: " . strip_tags($lesson->content ?? '') . "\n\n";
        }

        return $content;
    }

    /**
     * Prepare course content for LLM processing when no lessons exist.
     */
    private function prepareCourseContent(Course $course): string
    {
        $content = "Course Information:\n\n";
        $content .= "=== Course: {$course->title} ===\n";
        $content .= "Description: " . strip_tags($course->description ?? '') . "\n\n";
        
        return $content;
    }

    /**
     * Get reference resources for quiz generation.
     */
    private function getReferenceResources(array $resourceIds): array
    {
        try {
            $resources = Resource::whereIn('id', $resourceIds)->get();
            return $resources->map(function ($resource) {
                return [
                    'title' => $resource->title,
                    'content' => $resource->content
                ];
            })->toArray();
        } catch (Exception $e) {
            Log::warning('Failed to fetch reference resources', [
                'resource_ids' => $resourceIds,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Generate quizzes using LLM.
     */
    private function generateQuizzesWithLLM(string $lessonContent, array $referenceContent, array $quizTypes, int $quizCount, array $options): array
    {
        $prompt = $this->buildQuizGenerationPrompt($lessonContent, $referenceContent, $quizTypes, $quizCount, $options);
        
        // Log the prompt being sent to AI for debugging
        Log::info('Quiz generation prompt sent to AI', [
            'prompt_length' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 500),
            'quiz_types' => $quizTypes,
            'quiz_count' => $quizCount
        ]);
        
        try {
            $response = $this->ragService->generateJsonContent($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 4000
            ]);

            return $this->parseQuizResponse($response);
        } catch (Exception $e) {
            Log::warning('First attempt at quiz generation failed, trying with simpler prompt', [
                'error' => $e->getMessage()
            ]);
            
            // Try with a simpler prompt if the first attempt fails
            try {
                $simplePrompt = $this->buildSimpleQuizPrompt($lessonContent, $quizCount);
                $response = $this->ragService->generateJsonContent($simplePrompt, [
                    'temperature' => 0.5,
                    'max_tokens' => 2000
                ]);
                
                return $this->parseQuizResponse($response);
            } catch (Exception $e2) {
                throw new Exception("LLM quiz generation failed after retry: " . $e2->getMessage());
            }
        }
    }

    /**
     * Build the prompt for quiz generation.
     */
    private function buildQuizGenerationPrompt(string $lessonContent, array $referenceContent, array $quizTypes, int $quizCount, array $options): string
    {
        $prompt = "You are an expert quiz generator.\n\n";
        
        $prompt .= "Return ONLY valid JSON.\n";
        $prompt .= "Do not include explanations, markdown, or code fences.\n";
        $prompt .= "The JSON must be an array of exactly {$quizCount} quiz objects.\n\n";
        
        $prompt .= "Each quiz object must include:\n";
        $prompt .= "- type (one of: " . implode(', ', $quizTypes) . ")\n";
        $prompt .= "- question (string)\n";
        $prompt .= "- options (array of option objects, if applicable)\n";
        $prompt .= "- difficulty (one of: easy, medium, hard)\n\n";
        
        $prompt .= "Follow these exact formats:\n\n";
        
        if (in_array('MULTIPLE_CHOICE', $quizTypes)) {
            $prompt .= "MULTIPLE_CHOICE\n";
            $prompt .= "{\n";
            $prompt .= "  \"type\": \"MULTIPLE_CHOICE\",\n";
            $prompt .= "  \"question\": \"What is the capital of France?\",\n";
            $prompt .= "  \"options\": [\n";
            $prompt .= "    { \"text\": \"Berlin\", \"is_correct\": false },\n";
            $prompt .= "    { \"text\": \"Paris\", \"is_correct\": true },\n";
            $prompt .= "    { \"text\": \"Madrid\", \"is_correct\": false },\n";
            $prompt .= "    { \"text\": \"Rome\", \"is_correct\": false }\n";
            $prompt .= "  ],\n";
            $prompt .= "  \"difficulty\": \"easy\"\n";
            $prompt .= "}\n\n";
        }
        
        if (in_array('TYPE_ANSWER', $quizTypes)) {
            $prompt .= "TYPE_ANSWER\n";
            $prompt .= "{\n";
            $prompt .= "  \"type\": \"TYPE_ANSWER\",\n";
            $prompt .= "  \"question\": \"The chemical symbol for water is ____.\",\n";
            $prompt .= "  \"options\": [\n";
            $prompt .= "    { \"text\": \"H2O\", \"is_correct\": true },\n";
            $prompt .= "    { \"text\": \"CO2\", \"is_correct\": false },\n";
            $prompt .= "    { \"text\": \"O2\", \"is_correct\": false }\n";
            $prompt .= "  ],\n";
            $prompt .= "  \"difficulty\": \"medium\"\n";
            $prompt .= "}\n\n";
        }
        
        if (in_array('TRUE_FALSE', $quizTypes)) {
            $prompt .= "TRUE_FALSE\n";
            $prompt .= "{\n";
            $prompt .= "  \"type\": \"TRUE_FALSE\",\n";
            $prompt .= "  \"question\": \"The Earth is flat.\",\n";
            $prompt .= "  \"options\": [\n";
            $prompt .= "    { \"text\": \"True\", \"is_correct\": false },\n";
            $prompt .= "    { \"text\": \"False\", \"is_correct\": true }\n";
            $prompt .= "  ],\n";
            $prompt .= "  \"difficulty\": \"easy\"\n";
            $prompt .= "}\n\n";
        }
        
        if (in_array('MULTIPLE_SELECT', $quizTypes)) {
            $prompt .= "MULTIPLE_SELECT\n";
            $prompt .= "{\n";
            $prompt .= "  \"type\": \"MULTIPLE_SELECT\",\n";
            $prompt .= "  \"question\": \"Which of the following are programming languages?\",\n";
            $prompt .= "  \"options\": [\n";
            $prompt .= "    { \"text\": \"Python\", \"is_correct\": true },\n";
            $prompt .= "    { \"text\": \"JavaScript\", \"is_correct\": true },\n";
            $prompt .= "    { \"text\": \"HTML\", \"is_correct\": false },\n";
            $prompt .= "    { \"text\": \"Java\", \"is_correct\": true }\n";
            $prompt .= "  ],\n";
            $prompt .= "  \"difficulty\": \"medium\"\n";
            $prompt .= "}\n\n";
        }
        
        $prompt .= "Important:\n";
        $prompt .= "- Output must be a single valid JSON array of quiz objects.\n";
        $prompt .= "- Do not include any explanations, markdown, or code fences.\n";
        $prompt .= "- If you cannot comply, return an empty array [].\n\n";
        
        $prompt .= "Content to base questions on:\n";
        $prompt .= $lessonContent;
        
        if (!empty($referenceContent)) {
            $prompt .= "\n\nReference Resources:\n";
            foreach ($referenceContent as $resource) {
                $prompt .= "Title: {$resource['title']}\n";
                $prompt .= "Content: {$resource['content']}\n\n";
            }
        }

        return $prompt;
    }

    /**
     * Build a simpler prompt for quiz generation as fallback.
     */
    private function buildSimpleQuizPrompt(string $lessonContent, int $quizCount): string
    {
        $prompt = "Generate {$quizCount} quiz questions based on this content:\n\n";
        $prompt .= $lessonContent;
        $prompt .= "\n\nCRITICAL: Return ONLY valid JSON. Do not include explanations, markdown, or code fences.\n\n";
        $prompt .= "Important: Your entire response must be a single valid JSON array. Do not include markdown, explanations, or comments. If you cannot follow this, output an empty JSON array [].\n\n";
        $prompt .= "Return ONLY a JSON array with this format:\n";
        $prompt .= "[{\"type\":\"MULTIPLE_CHOICE\",\"question\":\"Your question here?\",\"options\":[{\"text\":\"Option A\",\"is_correct\":false},{\"text\":\"Option B\",\"is_correct\":true},{\"text\":\"Option C\",\"is_correct\":false},{\"text\":\"Option D\",\"is_correct\":false}],\"correct_answer\":\"Option B\",\"difficulty\":\"medium\",\"explanation\":\"Why this is correct\"},{\"type\":\"TRUE_FALSE\",\"question\":\"This statement is true.\",\"is_correct\":true,\"correct_answer\":\"true\",\"difficulty\":\"easy\",\"explanation\":\"This is a true statement.\"}]\n\n";
        $prompt .= "Do NOT wrap in HTML tags, markdown, or any other formatting. Return ONLY the JSON array.";
        
        return $prompt;
    }

    /**
     * Parse the LLM response and validate quiz structure.
     */
    private function parseQuizResponse(string $response): array
    {
        try {
            Log::info('Raw LLM response for quiz generation', [
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 500)
            ]);

            // With JSON mode, the response should be clean JSON
            $response = trim($response);
            
            // Try to parse the JSON directly
            $quizzes = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON parsing failed with JSON mode', [
                    'error' => json_last_error_msg(),
                    'response' => $response
                ]);
                
                // Fallback to cleaning if JSON mode didn't work
                $response = $this->fixCommonJsonIssues($response);
                $quizzes = json_decode($response, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON parsing failed after cleaning', [
                        'error' => json_last_error_msg(),
                        'cleaned_response' => $response,
                        'cleaned_response_length' => strlen($response),
                        'cleaned_response_bytes' => bin2hex($response)
                    ]);
                    
                    // Try to manually fix the JSON by removing any problematic characters
                    $response = $this->manualJsonFix($response);
                    $quizzes = json_decode($response, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('JSON parsing failed after manual fix', [
                            'error' => json_last_error_msg(),
                            'manually_fixed_response' => $response
                        ]);
                        
                        // Try to create a fallback quiz if JSON parsing fails
                        return $this->createFallbackQuiz($response);
                    }
                }
            }

            if (!is_array($quizzes)) {
                throw new Exception("Response is not an array");
            }

            // Validate each quiz
            foreach ($quizzes as $index => $quiz) {
                $this->validateQuizStructure($quiz, $index);
            }

            return $quizzes;
        } catch (Exception $e) {
            Log::error('Quiz response parsing failed', [
                'error' => $e->getMessage(),
                'response' => substr($response, 0, 1000)
            ]);
            throw new Exception("Failed to parse quiz response: " . $e->getMessage());
        }
    }

    /**
     * Fix common JSON issues in LLM responses.
     */
    private function fixCommonJsonIssues(string $json): string
    {
        // Remove HTML tags first
        $json = strip_tags($json);
        
        // Remove markdown code blocks if present
        if (str_starts_with($json, '```json')) {
            $json = substr($json, 7);
        }
        if (str_starts_with($json, '```')) {
            $json = substr($json, 3);
        }
        if (str_ends_with($json, '```')) {
            $json = substr($json, 0, -3);
        }
        $json = trim($json);
        
        // Try to extract JSON array from the response - use greedy match to get the full array
        if (preg_match('/\[[\s\S]*\]/', $json, $matches)) {
            $json = $matches[0];
        }
        
        // Fix trailing commas
        $json = preg_replace('/,(\s*[}\]])/', '$1', $json);
        
        // Fix double-escaped quotes (\"\" -> \")
        $json = preg_replace('/\\\\"/', '"', $json);
        
        // Fix single quotes to double quotes (but be more careful)
        $json = preg_replace("/(?<!\\\\)'/", '"', $json);
        
        // Remove any text before the first [ or after the last ]
        $json = preg_replace('/^[^[]*/', '', $json);
        $json = preg_replace('/[^\]]*$/', '', $json);
        
        // Fix missing commas between array elements (}{ -> },{)
        $json = preg_replace('/}\s*{/', '},{', $json);

        // Remove accidental leading commas after object/array starts
        $json = preg_replace('/\{\s*,\s*/', '{', $json);
        $json = preg_replace('/\[\s*,\s*/', '[', $json);

        // Fix truncated JSON by ensuring it ends properly
        $json = $this->fixTruncatedJson($json);
        
        return trim($json);
    }

    /**
     * Manually fix JSON by removing problematic characters and ensuring proper structure.
     */
    private function manualJsonFix(string $json): string
    {
        // Remove any non-printable characters except newlines and tabs
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $json);
        
        // Remove any BOM
        $json = str_replace("\xEF\xBB\xBF", '', $json);
        
        // Ensure proper UTF-8 encoding
        $json = mb_convert_encoding($json, 'UTF-8', 'UTF-8');
        
        // Remove any remaining HTML entities
        $json = html_entity_decode($json, ENT_QUOTES, 'UTF-8');
        
        // Fix any remaining quote issues
        $json = preg_replace('/\\\\"/', '"', $json);
        
        // Ensure it starts and ends properly
        $json = trim($json);
        if (!str_starts_with($json, '[')) {
            $json = '[' . $json;
        }
        if (!str_ends_with($json, ']')) {
            $json = $json . ']';
        }
        
        return $json;
    }

    /**
     * Aggressively clean JSON that still has parsing issues.
     */
    private function aggressiveJsonClean(string $json): string
    {
        // Remove all non-printable characters except newlines and tabs
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $json);
        
        // Remove any remaining HTML entities
        $json = html_entity_decode($json, ENT_QUOTES, 'UTF-8');
        
        // Ensure proper UTF-8 encoding
        $json = mb_convert_encoding($json, 'UTF-8', 'UTF-8');
        
        // Remove any BOM
        $json = str_replace("\xEF\xBB\xBF", '', $json);
        
        // Don't try to extract JSON array - just clean what we have
        // The issue might be that we're truncating valid JSON
        
        // Fix any remaining quote issues
        $json = preg_replace('/\\\\"/', '"', $json);
        
        // Ensure it starts and ends properly
        $json = trim($json);
        if (!str_starts_with($json, '[')) {
            $json = '[' . $json;
        }
        if (!str_ends_with($json, ']')) {
            $json = $json . ']';
        }
        
        return $json;
    }

    /**
     * Fix truncated JSON by ensuring proper closing.
     */
    private function fixTruncatedJson(string $json): string
    {
        // Count opening and closing braces/brackets
        $openBraces = substr_count($json, '{');
        $closeBraces = substr_count($json, '}');
        $openBrackets = substr_count($json, '[');
        $closeBrackets = substr_count($json, ']');
        
        // Add missing closing brackets/braces
        $missingBraces = $openBraces - $closeBraces;
        $missingBrackets = $openBrackets - $closeBrackets;
        
        // If we're missing closing brackets, add them
        if ($missingBrackets > 0) {
            $json .= str_repeat(']', $missingBrackets);
        }
        
        // If we're missing closing braces, add them
        if ($missingBraces > 0) {
            $json .= str_repeat('}', $missingBraces);
        }
        
        // If the JSON doesn't end with ], add it
        if (!str_ends_with(trim($json), ']')) {
            $json = rtrim($json) . ']';
        }
        
        return $json;
    }

    /**
     * Create a fallback quiz when JSON parsing fails.
     */
    private function createFallbackQuiz(string $response): array
    {
        Log::warning('Creating fallback quiz due to JSON parsing failure', [
            'response_preview' => substr($response, 0, 200)
        ]);

        // Create a simple fallback quiz
        return [
            [
                'type' => 'MULTIPLE_CHOICE',
                'question' => 'Based on the course content, what is the main topic covered?',
                'options' => [
                    ['text' => 'General knowledge', 'is_correct' => false],
                    ['text' => 'Course-specific content', 'is_correct' => true],
                    ['text' => 'Random information', 'is_correct' => false],
                    ['text' => 'Unrelated topics', 'is_correct' => false]
                ],
                'correct_answer' => 'Course-specific content',
                'difficulty' => 'easy',
                'explanation' => 'This question was generated as a fallback when the AI response could not be parsed properly.'
            ]
        ];
    }

    /**
     * Validate the structure of a single quiz.
     */
    private function validateQuizStructure(array $quiz, int $index): void
    {
        $requiredFields = ['type', 'question'];
        foreach ($requiredFields as $field) {
            if (!isset($quiz[$field]) || empty($quiz[$field])) {
                throw new Exception("Quiz {$index} missing required field: {$field}");
            }
        }

        $validTypes = QuizQuestion::getTypes();
        if (!in_array($quiz['type'], $validTypes)) {
            throw new Exception("Quiz {$index} has invalid type: {$quiz['type']}");
        }

        // Validate options for multiple choice/select questions
        if (in_array($quiz['type'], [QuizQuestion::TYPE_MULTIPLE_CHOICE, QuizQuestion::TYPE_MULTIPLE_SELECT])) {
            if (!isset($quiz['options']) || !is_array($quiz['options']) || count($quiz['options']) < 2) {
                throw new Exception("Quiz {$index} missing or invalid options");
            }

            $correctCount = 0;
            foreach ($quiz['options'] as $option) {
                if (!isset($option['text']) || !isset($option['is_correct'])) {
                    throw new Exception("Quiz {$index} has invalid option structure");
                }
                if ($option['is_correct']) {
                    $correctCount++;
                }
            }

            if ($quiz['type'] === QuizQuestion::TYPE_MULTIPLE_CHOICE && $correctCount !== 1) {
                throw new Exception("Quiz {$index} must have exactly 1 correct answer for multiple choice");
            }

            if ($quiz['type'] === QuizQuestion::TYPE_MULTIPLE_SELECT && $correctCount < 2) {
                throw new Exception("Quiz {$index} must have at least 2 correct answers for multiple select");
            }

            // Remove the extra "correct_answers" field that AI sometimes adds
            if (isset($quiz['correct_answers'])) {
                unset($quiz['correct_answers']);
            }
        }

        // Validate TRUE_FALSE questions
        if ($quiz['type'] === QuizQuestion::TYPE_TRUE_FALSE) {
            // Handle both "answer" and "is_correct" formats
            if (isset($quiz['answer'])) {
                $quiz['is_correct'] = $quiz['answer'];
                $quiz['correct_answer'] = $quiz['answer'] ? 'true' : 'false';
            } elseif (isset($quiz['is_correct'])) {
                $quiz['correct_answer'] = $quiz['is_correct'] ? 'true' : 'false';
            } else {
                throw new Exception("Quiz {$index} TRUE_FALSE question missing answer field");
            }
        }
    }

    /**
     * Store generated quizzes in the database for a specific lesson.
     */
    private function storeGeneratedQuizzesForLesson(array $quizzes, int $lessonId, int $courseId): array
    {
        $storedQuizzes = [];
        $position = 1;

        foreach ($quizzes as $quizData) {
            try {
                // Create quiz question with lesson_id
                $question = QuizQuestion::create([
                    'lesson_id' => $lessonId,
                    'course_id' => $courseId,
                    'question' => $quizData['question'],
                    'type' => $quizData['type'],
                    'position' => $position++,
                    'metadata' => [
                        'difficulty' => $quizData['difficulty'] ?? 'medium',
                        'explanation' => $quizData['explanation'] ?? '',
                        'course_id' => $courseId,
                        'generated_at' => now()->toISOString()
                    ]
                ]);

                // Create options if applicable
                if (isset($quizData['options']) && is_array($quizData['options'])) {
                    $optionPosition = 1;
                    foreach ($quizData['options'] as $optionData) {
                        QuizOption::create([
                            'quiz_question_id' => $question->id,
                            'option_text' => $optionData['text'],
                            'is_correct' => $optionData['is_correct'],
                            'position' => $optionPosition++
                        ]);
                    }
                }

                // Store correct answer in metadata for type answer and puzzle questions
                if (in_array($quizData['type'], [QuizQuestion::TYPE_TYPE_ANSWER, QuizQuestion::TYPE_PUZZLE])) {
                    $metadata = $question->metadata;
                    $metadata['correct_answer'] = $quizData['correct_answer'] ?? '';
                    $question->update(['metadata' => $metadata]);
                }

                $storedQuizzes[] = $question->load('options');

            } catch (Exception $e) {
                Log::error('Failed to store quiz question for lesson', [
                    'lesson_id' => $lessonId,
                    'quiz_data' => $quizData,
                    'error' => $e->getMessage()
                ]);
                throw new Exception("Failed to store quiz: " . $e->getMessage());
            }
        }

        return $storedQuizzes;
    }

    /**
     * Store generated quizzes in the database.
     */
    private function storeGeneratedQuizzes(array $quizzes, int $courseId): array
    {
        $storedQuizzes = [];
        $position = 1;

        foreach ($quizzes as $quizData) {
            try {
                // Create quiz question
                $question = QuizQuestion::create([
                    'lesson_id' => null, // Will be assigned when lesson is selected
                    'question' => $quizData['question'],
                    'type' => $quizData['type'],
                    'position' => $position++,
                    'metadata' => [
                        'difficulty' => $quizData['difficulty'] ?? 'medium',
                        'explanation' => $quizData['explanation'] ?? '',
                        'course_id' => $courseId,
                        'generated_at' => now()->toISOString()
                    ]
                ]);

                // Create options if applicable
                if (isset($quizData['options']) && is_array($quizData['options'])) {
                    $optionPosition = 1;
                    foreach ($quizData['options'] as $optionData) {
                        QuizOption::create([
                            'quiz_question_id' => $question->id,
                            'option_text' => $optionData['text'],
                            'is_correct' => $optionData['is_correct'],
                            'position' => $optionPosition++
                        ]);
                    }
                }

                // Store correct answer in metadata for type answer and puzzle questions
                if (in_array($quizData['type'], [QuizQuestion::TYPE_TYPE_ANSWER, QuizQuestion::TYPE_PUZZLE])) {
                    $metadata = $question->metadata;
                    $metadata['correct_answer'] = $quizData['correct_answer'] ?? '';
                    $question->update(['metadata' => $metadata]);
                }

                $storedQuizzes[] = $question->load('options');

            } catch (Exception $e) {
                Log::error('Failed to store quiz question', [
                    'quiz_data' => $quizData,
                    'error' => $e->getMessage()
                ]);
                throw new Exception("Failed to store quiz: " . $e->getMessage());
            }
        }

        return $storedQuizzes;
    }

    /**
     * Assign generated quizzes to a specific lesson.
     */
    public function assignQuizzesToLesson(array $questionIds, int $lessonId): array
    {
        try {
            $questions = QuizQuestion::whereIn('id', $questionIds)->get();
            
            if ($questions->isEmpty()) {
                throw new Exception('No questions found with provided IDs');
            }

            $assignedQuestions = [];
            $position = 1;

            foreach ($questions as $question) {
                $question->update([
                    'lesson_id' => $lessonId,
                    'position' => $position++
                ]);
                $assignedQuestions[] = $question;
            }

            Log::info('Quizzes assigned to lesson', [
                'lesson_id' => $lessonId,
                'question_count' => count($assignedQuestions)
            ]);

            return $assignedQuestions;

        } catch (Exception $e) {
            Log::error('Failed to assign quizzes to lesson', [
                'question_ids' => $questionIds,
                'lesson_id' => $lessonId,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to assign quizzes: " . $e->getMessage());
        }
    }

    /**
     * Get available quiz types.
     */
    public function getAvailableQuizTypes(): array
    {
        return QuizQuestion::getTypes();
    }

    /**
     * Get quiz generation statistics for a course.
     */
    public function getQuizStats(int $courseId): array
    {
        $totalQuestions = QuizQuestion::whereHas('lesson', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();

        $questionsByType = QuizQuestion::whereHas('lesson', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->selectRaw('type, COUNT(*) as count')
          ->groupBy('type')
          ->pluck('count', 'type')
          ->toArray();

        return [
            'total_questions' => $totalQuestions,
            'questions_by_type' => $questionsByType,
            'available_types' => $this->getAvailableQuizTypes()
        ];
    }

    /**
     * Check if a course has content available for quiz generation.
     */
    public function checkCourseContent(int $courseId): array
    {
        $course = Course::findOrFail($courseId);
        $lessons = $this->fetchCourseLessons($courseId);
        
        $hasLessons = $lessons->isNotEmpty();
        $hasCourseDescription = !empty(trim($course->description));
        
        return [
            'course_id' => $courseId,
            'course_title' => $course->title,
            'has_lessons' => $hasLessons,
            'lesson_count' => $lessons->count(),
            'has_course_description' => $hasCourseDescription,
            'can_generate_quizzes' => $hasLessons || $hasCourseDescription
        ];
    }
}
