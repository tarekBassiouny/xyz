<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SyncRolePermissionsRequest extends FormRequest
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
        return [
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
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
            'permission_ids' => [
                'description' => 'List of permission IDs to assign to the role.',
                'example' => [1, 2, 3],
            ],
        ];
    }
}
