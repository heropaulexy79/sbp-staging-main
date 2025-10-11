<?php

namespace App\Http\Requests\Lesson;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $organisation = $user->organisation();
        $course = $this->route('course');

        return $user->can('update', $organisation) && $organisation->id === $course->organisation_id;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'is_published' => $this->is_published === 'true' ? true : false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $course = $this->route('course');
        $lesson = $this->route('lesson');

        $rules = [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                // unique:post,slug,except,id
                !$lesson ? Rule::unique('lessons', 'slug')->where('course_id', $course->id) : "max:255",
                'max:255'
            ],
            'type' => 'nullable|in:QUIZ,DEFAULT',
            'is_published' => 'boolean',

            'content' => [Rule::requiredIf($this->input('type', Lesson::TYPE_DEFAULT) === Lesson::TYPE_DEFAULT), 'nullable', 'string'],
        ];

        $quizRules = [
            // 'quiz.*.text' => [
            //     Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
            //     'string',
            //     'min:3',
            //     'max:255',
            // ],
            // 'quiz.*.type' => [
            //     Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
            //     Rule::in(['multiple_choice', 'single_choice']),
            // ],
            // 'quiz.*.options' => [
            //     Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
            //     'array',
            //     'min:2',
            //     'max:10',
            // ],
            // 'quiz.*.options.*' => [
            //     Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
            //     'string',
            //     'min:2',
            //     'max:255',
            // ],
            // 'quiz.*.correct_option' => [
            //     Rule::requiredIf(function ($data) {
            //         return $data['type'] === 'multiple_choice';
            //     }),
            //     Rule::isArrayIf(function ($data) {
            //         return $data['type'] === 'multiple_choice';
            //     }),
            //     Rule::exists('question_options', 'id')
            //         ->where('question_id', Rule::exists('quiz', 'id')), // Ensure correct options exist for the question
            // ],
            // 'quiz.*.correct_option.*' => [ // Validate individual correct options (multiple choice only)
            //     Rule::requiredIf(function ($data, $key, $row) {
            //         return $row['type'] === 'multiple_choice';
            //     }),
            //     Rule::exists('question_options', 'id')
            //         ->where('question_id', Rule::exists('quiz', 'id')), // Ensure correct options exist for the question
            // ],

            'quiz' => [
                Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
                'array',
                'min:1', // Adjust minimum length as needed
                'max:30', // Adjust maximum length as needed
            ],
            'quiz.*.text' => [
                Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
                'string',
                'min:3', // Adjust minimum length as needed
                'max:255', // Adjust maximum length as needed
            ],
            'quiz.*.type' => [
                Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
                Rule::in(['multiple_choice', 'single_choice']),
            ],
            'quiz.*.options' => [
                Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
                'array',
                'min:2', // Ensure at least two options
                'max:10', // Adjust maximum number of options as needed
            ],
            'quiz.*.options.*.text' => [
                Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
                'string',
                'min:1', // Adjust minimum length as needed
                'max:255', // Adjust maximum length as needed
            ],
            'quiz.*.correct_option' => [
                Rule::requiredIf($this->input('type') === Lesson::TYPE_QUIZ),
                // Rule::requiredIf(function() {
                //     return $data['type'] === 'multiple_choice';
                // }),
                // Rule::isArrayIf(function ($data) {
                //     return $data['type'] === 'multiple_choice';
                // }),
                // Rule::custom(function ($attribute, $value, $validator) {
                //     $options = request()->input('quiz.' . $validator->getIndex() . '.options');
                //     // Check if all elements in correct_option exist within options (by text comparison)
                //     return collect($value)->every(function ($correct_optionText) use ($options) {
                //         return collect($options)->contains('text', $correct_optionText);
                //     });
                // }),
            ],
        ];

        return $this->input('type') === Lesson::TYPE_QUIZ ? array_merge($rules, $quizRules) : $rules;
    }

    public function messages()
    {
        return [
            'is_published.boolean' => 'Status field must be true or false',
            'quiz.*.text.required' => 'Question field is required',
            'quiz.*.text.min' => 'Question field has a minimum of :min',
            'quiz.*.text.max' => 'Question field has a maximum of :max',
            'quiz.*.type.required' => 'Type field is required',
            'quiz.*.type.in' => 'Type field can only be :values',
            'quiz.*.options.required' => 'Options field is required',
            'quiz.*.options.*.text.required' => 'Option field is required',
            'quiz.*.options.*.text.min' => 'Option field has a minimum of :min',
            'quiz.*.options.*.text.max' => 'Option field has a maximum of :max',
            'quiz.*.correct_option.required' => 'Correct option is required',
        ];
    }
}
