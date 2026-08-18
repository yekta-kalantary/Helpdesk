<?php

namespace Modules\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRecoveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
