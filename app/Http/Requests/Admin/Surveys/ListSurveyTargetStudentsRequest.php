<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Surveys;

use App\Enums\CenterType;
use App\Enums\SurveyScopeType;
use App\Http\Requests\Admin\AdminListRequest;
use App\Models\Center;
use App\Models\User;
use App\Support\Filters\FilterInput;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ListSurveyTargetStudentsRequest extends AdminListRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        $scopeType = (int) $this->input('scope_type');

        if ($scopeType === SurveyScopeType::System->value) {
            return $user->hasRole('super_admin');
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->listRules(), [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'scope_type' => ['required', 'integer', Rule::in(array_column(SurveyScopeType::cases(), 'value'))],
            'center_id' => [
                'nullable',
                'integer',
                'exists:centers,id',
                'required_if:scope_type,'.SurveyScopeType::Center->value,
            ],
            'status' => ['sometimes', 'integer', 'in:0,1,2'],
            'search' => ['sometimes', 'string'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $scopeType = (int) $this->input('scope_type');
            $centerId = FilterInput::intOrNull($this->all(), 'center_id');

            if ($scopeType !== SurveyScopeType::System->value || $centerId === null) {
                return;
            }

            $isUnbranded = Center::query()
                ->whereKey($centerId)
                ->where('type', CenterType::Unbranded->value)
                ->exists();

            if ($isUnbranded) {
                return;
            }

            $validator->errors()->add('center_id', 'System survey targeting supports only unbranded centers.');
        });
    }

    /**
     * @return array{
     *     scope_type: SurveyScopeType,
     *     center_id: int|null,
     *     status: int|null,
     *     search: string|null,
     *     page: int,
     *     per_page: int
     * }
     */
    public function filters(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->validated();

        return [
            'scope_type' => SurveyScopeType::from((int) $data['scope_type']),
            'center_id' => FilterInput::intOrNull($data, 'center_id'),
            'status' => FilterInput::intOrNull($data, 'status'),
            'search' => FilterInput::stringOrNull($data, 'search'),
            'page' => FilterInput::page($data),
            'per_page' => FilterInput::perPage($data),
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function queryParameters(): array
    {
        return [
            'scope_type' => [
                'description' => 'Required scope type: 1 for system targeting, 2 for center targeting.',
                'example' => '1',
            ],
            'center_id' => [
                'description' => 'Required when scope_type=2. Optional when scope_type=1 (unbranded only).',
                'example' => '10',
            ],
            'status' => [
                'description' => 'Optional student status filter (0 inactive, 1 active, 2 banned).',
                'example' => '1',
            ],
            'search' => [
                'description' => 'Optional search by name, username, email, or phone.',
                'example' => 'ahmed',
            ],
            'per_page' => [
                'description' => 'Items per page (max 50, recommended 20 for infinite scroll).',
                'example' => '20',
            ],
            'page' => [
                'description' => 'Page number.',
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
                'code' => 'FORBIDDEN',
                'message' => 'Only super admins can target students for system surveys.',
            ],
        ], 403));
    }
}
