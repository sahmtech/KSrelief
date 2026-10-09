<?php

namespace App\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BroadcastPushNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('push.broadcast') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:2000'],
            'target' => ['required', Rule::in(['all_doctors', 'user_ids', 'campaign_doctors'])],
            'user_ids' => ['required_if:target,user_ids', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'campaign_id' => ['required_if:target,campaign_doctors', 'nullable', 'integer', 'exists:campaigns,id'],
            'data' => ['nullable', 'array'],
        ];
    }
}
