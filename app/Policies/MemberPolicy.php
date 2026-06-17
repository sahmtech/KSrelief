<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('member.view');
    }

    public function view(User $user, Member $member): bool
    {
        return $user->can('member.view');
    }

    public function create(User $user): bool
    {
        return $user->can('member.create');
    }

    public function update(User $user, Member $member): bool
    {
        return $user->can('member.update');
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->can('member.delete');
    }

    public function assignCampaign(User $user, Member $member): bool
    {
        return $user->can('member.assign_campaign');
    }

    public function import(User $user): bool
    {
        return $user->can('member.import_excel');
    }
}
