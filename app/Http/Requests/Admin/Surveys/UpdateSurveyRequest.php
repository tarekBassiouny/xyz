<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Surveys;

use App\Enums\SurveyQuestionType;
use App\Enums\SurveyType;
use App\Models\Survey;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title_translations' => ['sometimes', 'array'],
            'title_translations.en' => ['required_with:title_translations', 'string', 'max:255'],
            'title_translations.ar' => ['required_with:title_translations', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.en' => ['nullable', 'string'],
            'description_translations.ar' => ['nullable', 'string'],
            'type' => ['sometimes', 'integer', Rule::in(array_column(SurveyType::cases(), 'value'))],
            'is_active' => ['sometimes', 'boolean'],
            'is_mandatory' => ['sometimes', 'boolean'],
            'allow_multiple_submissions' => ['sometimes', 'boolean'],
            'start_at' => ['nullable', 'date_format:Y-m-d'],
            'end_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_at'],

            'questions' => ['sometimes', 'array', 'min:1', 'max:10'],
            'questions.*.question_translations' => ['required_with:questions', 'array'],
            'questions.*.question_translations.en' => ['required_with:questions', 'string'],
            'questions.*.question_translations.ar' => ['required_with:questions', 'string'],
            'questions.*.type' => ['required_with:questions', 'integer', Rule::in(array_column(SurveyQuestionType::cases(), 'value'))],
            'questions.*.is_required' => ['sometimes', 'boolean'],
            'questions.*.order_index' => ['sometimes', 'integer', 'min:0'],

            'questions.*.options' => ['sometimes', 'array'],
            'questions.*.options.*.option_translations' => ['required_with:questions.*.options', 'array'],
            'questions.*.options.*.option_translations.en' => ['required_with:questions.*.options', 'string'],
            'questions.*.options.*.option_translations.ar' => ['required_with:questions.*.options', 'string'],
            'questions.*.options.*.order_index' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $questions = $this->input('questions', []);
            foreach ($questions as $index => $question) {
                $type = SurveyQuestionType::tryFrom((int) ($question['type'] ?? 0));
                if ($type !== null && $type->requiresOptions()) {
                    $options = $question['options'] ?? [];
                    if (empty($options)) {
                        $validator->errors()->add(
                            sprintf('questions.%s.options', $index),
                            'Options are required for single choice and multiple choice questions.'
                        );
                    }
                }
            }

            /** @var Survey|mixed $survey */
            $survey = $this->route('survey');
            $hasStartAtInput = $this->exists('start_at');
            $hasEndAtInput = $this->exists('end_at');

            $startAtInput = $hasStartAtInput
                ? $this->input('start_at')
                : ($survey instanceof Survey ? $survey->start_at?->toDateString() : null);

            $endAtInput = $hasEndAtInput
                ? $this->input('end_at')
                : ($survey instanceof Survey ? $survey->end_at?->toDateString() : null);

            try {
                $startAt = ($startAtInput === null || $startAtInput === '')
                    ? null
                    : Carbon::parse((string) $startAtInput);

                $endAt = ($endAtInput === null || $endAtInput === '')
                    ? null
                    : Carbon::parse((string) $endAtInput);
            } catch (\Throwable) {
                return;
            }

            if ($startAt === null || $endAt === null || $endAt->greaterThanOrEqualTo($startAt)) {
                return;
            }

            if ($hasStartAtInput && ! $hasEndAtInput) {
                $validator->errors()->add('start_at', 'Start date must be before or equal to end date.');

                return;
            }

            $validator->errors()->add('end_at', 'End date must be after or equal to start date.');
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'title_translations' => [
                'description' => 'Survey title in different languages.',
                'example' => ['en' => 'Updated survey title', 'ar' => 'عنوان الاستبيان المحدث'],
            ],
            'description_translations' => [
                'description' => 'Survey description in different languages.',
                'example' => ['en' => 'Updated description', 'ar' => 'وصف محدث'],
            ],
            'type' => [
                'description' => 'Survey type: 1=Feedback, 2=Mandatory, 3=Poll.',
                'example' => 1,
            ],
            'is_active' => [
                'description' => 'Whether the survey is active.',
                'example' => true,
            ],
            'is_mandatory' => [
                'description' => 'Whether completing the survey is mandatory.',
                'example' => false,
            ],
            'allow_multiple_submissions' => [
                'description' => 'Whether a student can submit multiple responses.',
                'example' => false,
            ],
            'start_at' => [
                'description' => 'Survey start date (YYYY-MM-DD).',
                'example' => '2026-02-15',
            ],
            'end_at' => [
                'description' => 'Survey end date (YYYY-MM-DD).',
                'example' => '2026-03-01',
            ],
            'questions' => [
                'description' => 'Optional full replacement for survey questions.',
                'example' => [
                    [
                        'question_translations' => ['en' => 'How satisfied are you?', 'ar' => 'ما مدى رضاك؟'],
                        'type' => 3,
                        'is_required' => true,
                        'order_index' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validation failed',
                'details' => $validator->errors(),
            ],
        ], 422));
    }
}
