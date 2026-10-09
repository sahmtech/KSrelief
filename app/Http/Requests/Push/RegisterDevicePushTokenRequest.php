<?php

namespace App\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterDevicePushTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'min:20', 'max:512'],
            'platform' => ['required', Rule::in(['android', 'ios'])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
