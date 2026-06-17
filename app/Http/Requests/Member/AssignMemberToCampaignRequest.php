<?php

namespace App\Http\Requests\Member;

use App\Models\Campaign;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMemberToCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('member.assign_campaign') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'assigned_role' => ['nullable', 'string', 'max:255'],
            'assigned_from' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'date', 'after_or_equal:assigned_from'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        if ($this->has('campaign_id')) {
            $rules['campaign_id'] = ['required', 'integer', Rule::exists('campaigns', 'id')];
        }

        if ($this->has('member_id')) {
            $rules['member_id'] = ['required', 'integer', Rule::exists('members', 'id')];
        }

        return $rules;
    }

    public function resolveCampaign(): Campaign
    {
        if ($this->route('campaign') instanceof Campaign) {
            return $this->route('campaign');
        }

        return Campaign::query()->findOrFail($this->validated('campaign_id'));
    }

    public function resolveMember(): Member
    {
        if ($this->route('member') instanceof Member) {
            return $this->route('member');
        }

        return Member::query()->findOrFail($this->validated('member_id'));
    }
}
