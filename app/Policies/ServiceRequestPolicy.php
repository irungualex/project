<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isAdmin() || (int) $serviceRequest->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }
}
