<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function view(User $user, Deal $deal): bool
    {
        return $user->id === $deal->user_id || $user->role === UserRole::Admin;
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->id === $deal->user_id || $user->role === UserRole::Admin;
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->id === $deal->user_id || $user->role === UserRole::Admin;
    }
}
