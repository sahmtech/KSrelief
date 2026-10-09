<?php

namespace App\Http\Requests\Administration;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('changePassword', $user) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => __('users.validation.password_required'),
            'password.confirmed' => __('users.validation.password_confirmed'),
            'password' => __('users.validation.password_rules'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $user = $this->route('user');

        if ($user instanceof User) {
            session()->flash('open_change_password_user_id', $user->id);
        }

        throw new ValidationException($validator);
    }
}
