<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Sections;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sort_order') && ! $this->has('order_index')) {
            $this->merge([
                'order_index' => $this->input('sort_order'),
            ]);
        }

        if (! $this->has('course_id') && $this->route('course')) {
            $course = $this->route('course');
            $courseId = null;

            if ($course instanceof \App\Models\Course) {
                $courseId = $course->getKey();
            } elseif (is_numeric($course)) {
                $courseId = (int) $course;
            }

            if ($courseId !== null) {
                $this->merge([
                    'course_id' => $courseId,
                ]);
            }
        }
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order_index' => ['nullable', 'integer', 'min:0'],
            'title_translations' => ['sometimes', 'array'],
            'description_translations' => ['sometimes', 'array'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'course_id' => [
                'description' => 'ID of the parent course.',
                'example' => 1,
            ],
            'title' => [
                'description' => 'Section title (base locale string).',
                'example' => 'Introduction',
            ],
            'description' => [
                'description' => 'Section description (base locale string).',
                'example' => 'Overview of the course.',
            ],
            'order_index' => [
                'description' => 'Optional ordering index.',
                'example' => 1,
            ],
            'title_translations' => [
                'description' => 'Localized titles keyed by locale.',
                'example' => ['en' => 'Introduction'],
            ],
            'description_translations' => [
                'description' => 'Localized descriptions keyed by locale.',
                'example' => ['en' => 'Overview of the course'],
            ],
        ];
    }
}
