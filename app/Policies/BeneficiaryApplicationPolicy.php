<?php

namespace App\Policies;

use App\Models\BeneficiaryApplication;
use App\Models\User;

class BeneficiaryApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('beneficiary_application.view');
    }

    public function view(User $user, BeneficiaryApplication $application): bool
    {
        return $user->can('beneficiary_application.view');
    }

    public function review(User $user, ?BeneficiaryApplication $application = null): bool
    {
        return $user->can('beneficiary_application.review');
    }
}
