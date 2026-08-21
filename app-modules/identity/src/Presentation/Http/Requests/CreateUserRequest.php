<?php

namespace Modules\Identity\Presentation\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Identity\Domain\Enums\UserRole;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->is_active === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'client_id' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('role') === UserRole::Customer->value),
                Rule::prohibitedIf(fn (): bool => $this->input('role') !== UserRole::Customer->value),
                Rule::exists('clients', 'id')->where(fn (Builder $query): Builder => $query->where('status', 'active')),
            ],
            'is_active' => ['required', 'boolean'],
            'password_mode' => ['required', Rule::in(['manual', 'email'])],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                Rule::requiredIf(fn (): bool => $this->input('password_mode') === 'manual'),
                Rule::prohibitedIf(fn (): bool => $this->input('password_mode') === 'email'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('identity::messages.validation.name_required'),
            'last_name.required' => __('identity::messages.validation.last_name_required'),
            'email.required' => __('identity::messages.validation.email_required'),
            'email.email' => __('identity::messages.validation.email_invalid'),
            'email.unique' => __('identity::messages.validation.email_unique'),
        ];
    }
}
