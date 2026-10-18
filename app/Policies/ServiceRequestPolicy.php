<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }
}
