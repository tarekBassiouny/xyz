<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->hasPermission('role.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Role|null $role */
        $role = $this->route('role');
        $roleId = $role?->id;

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['sometimes', 'string', 'max:100', Rule::unique('roles', 'slug')->ignore($roleId)],
            'description' => ['nullable', 'string', 'max:255'],
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

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => [
                'code' => 'PERMISSION_DENIED',
                'message' => 'You do not have permission to perform this action.',
            ],
        ], 403));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Role display name.',
                'example' => 'Support Admin',
            ],
            'slug' => [
                'description' => 'Unique role identifier.',
                'example' => 'support_admin',
            ],
            'description' => [
                'description' => 'Optional role description.',
                'example' => 'Handles support workflows.',
            ],
        ];
    }
}
