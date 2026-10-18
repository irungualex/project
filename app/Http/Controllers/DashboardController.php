<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $requests = ServiceRequest::query()
            ->visibleTo($user)
            ->with('citizen:id,name')
            ->latest()
            ->limit(5)
            ->get();

        $scope = fn (): Builder => ServiceRequest::query()->visibleTo($user);

        return view('dashboard', [
            'requests' => $requests,
            'total' => $scope()->count(),
            'open' => $scope()->where('status', '!=', 'Resolved')->count(),
            'pending' => $scope()->where('status', 'Pending')->count(),
            'inProgress' => $scope()->where('status', 'In Progress')->count(),
            'resolved' => $scope()->where('status', 'Resolved')->count(),
            'citizens' => $user->isAdmin()
                ? $scope()->whereNotNull('citizen_name')->distinct()->count('citizen_name')
                : $scope()->where('status', 'In Progress')->count(),
        ]);
    }
}
