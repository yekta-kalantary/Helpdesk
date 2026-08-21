<?php

namespace Modules\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Clients\Application\DTOs\ClientSummary;
use Modules\Clients\Application\Queries\ActiveClientDirectory;
use Modules\Identity\Domain\Enums\UserRole;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->is_active === true && $user->isAdmin();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $activeClientIds = $this->activeClientIds();

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
                Rule::in($activeClientIds),
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
            'mobile.max' => __('identity::messages.users_page.validation.mobile_max'),
            'role.required' => __('identity::messages.users_page.validation.role_required'),
            'role.enum' => __('identity::messages.users_page.validation.role_invalid'),
            'client_id.required' => __('identity::messages.validation.client_required'),
            'client_id.in' => __('identity::messages.validation.client_active'),
            'client_id.prohibited' => __('identity::messages.validation.client_prohibited'),
            'client_id.prohibited_if' => __('identity::messages.validation.client_prohibited'),
            'is_active.required' => __('identity::messages.users_page.validation.active_required'),
            'is_active.boolean' => __('identity::messages.users_page.validation.active_invalid'),
            'password.required' => __('identity::messages.users_page.validation.password_required'),
            'password.min' => __('identity::messages.users_page.validation.password_min'),
            'password.confirmed' => __('identity::messages.users_page.validation.password_confirmed'),
            'password.prohibited' => __('identity::messages.users_page.validation.password_prohibited'),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function activeClientIds(): array
    {
        $clientId = $this->input('client_id');

        if (! is_numeric($clientId) || (int) $clientId < 1) {
            return [];
        }

        return collect(app(ActiveClientDirectory::class)->executeForIds([(int) $clientId]))
            ->map(fn (ClientSummary $client): int => $client->id)
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'client_id' => __('identity::messages.validation.client_id'),
        ];
    }
}
