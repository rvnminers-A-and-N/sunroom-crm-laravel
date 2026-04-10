<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function view(User $user, Activity $activity): bool
    {
        return $user->id === $activity->user_id || $user->role === UserRole::Admin;
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->id === $activity->user_id || $user->role === UserRole::Admin;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->id === $activity->user_id || $user->role === UserRole::Admin;
    }
}
