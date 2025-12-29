<?php

declare(strict_types=1);

namespace App\Http\Requests\Mobile;

use App\Filters\Mobile\CenterFilters;
use Illuminate\Foundation\Http\FormRequest;

class ListCentersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string'],
            'is_featured' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function filters(): CenterFilters
    {
        /** @var array<string, mixed> $data */
        $data = $this->validated();
        $term = isset($data['search']) ? trim((string) $data['search']) : null;

        $isFeatured = null;
        if (array_key_exists('is_featured', $data)) {
            $isFeatured = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return new CenterFilters(
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 15),
            search: $term !== '' ? $term : null,
            isFeatured: $isFeatured,
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'Search centers by name or description.',
                'example' => 'Science',
            ],
            'is_featured' => [
                'description' => 'Filter centers by featured status.',
                'example' => '0',
            ],
            'per_page' => [
                'description' => 'Items per page (max 100).',
                'example' => '15',
            ],
            'page' => [
                'description' => 'Page number to retrieve.',
                'example' => '1',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
