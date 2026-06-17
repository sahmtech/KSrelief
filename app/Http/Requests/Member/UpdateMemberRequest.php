<?php

namespace App\Http\Requests\Member;

use App\Models\Member;

class UpdateMemberRequest extends StoreMemberRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');

        return $member instanceof Member
            && ($this->user()?->can('update', $member) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $member = $this->route('member');

        return [
            ...parent::memberRules(),
            'mobile' => ['required', 'string', 'max:30', \Illuminate\Validation\Rule::unique('members', 'mobile')->ignore($member?->id)],
            'email' => ['nullable', 'email', 'max:255', \Illuminate\Validation\Rule::unique('members', 'email')->ignore($member?->id)],
            'user_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('users', 'id'),
                \Illuminate\Validation\Rule::unique('members', 'user_id')->ignore($member?->id),
            ],
        ];
    }
}
