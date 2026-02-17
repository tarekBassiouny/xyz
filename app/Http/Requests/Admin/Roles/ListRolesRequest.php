<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Roles;

use App\Filters\Admin\RoleFilters;
use App\Http\Requests\Admin\AdminListRequest;
use App\Support\Filters\FilterInput;

class ListRolesRequest extends AdminListRequest
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
        return array_merge($this->listRules(), [
            'search' => ['sometimes', 'string', 'max:100'],
        ]);
    }

    public function filters(): RoleFilters
    {
        /** @var array<string, mixed> $data */
        $data = $this->validated();

        return new RoleFilters(
            page: FilterInput::page($data),
            perPage: FilterInput::perPage($data),
            search: FilterInput::stringOrNull($data, 'search')
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'Items per page (max 100).',
                'example' => '15',
            ],
            'page' => [
                'description' => 'Page number to retrieve.',
                'example' => '1',
            ],
            'search' => [
                'description' => 'Optional search term applied to role slug or name.',
                'example' => 'support',
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
