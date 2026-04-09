<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->id === $company->user_id || $user->role === UserRole::Admin;
    }

    public function update(User $user, Company $company): bool
    {
        return $user->id === $company->user_id || $user->role === UserRole::Admin;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->id === $company->user_id || $user->role === UserRole::Admin;
    }
}
