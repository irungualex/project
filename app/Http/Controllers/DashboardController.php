<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', $this->dashboardData($user));
    }

    public function admin(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('admin.dashboard', $this->dashboardData($request->user()));
    }

    /** @return array<string, mixed> */
    private function dashboardData(User $user): array
    {
        $requests = ServiceRequest::query()
            ->visibleTo($user)
            ->with('citizen:id,name')
            ->latest()
            ->limit(6)
            ->get();

        $scope = fn (): Builder => ServiceRequest::query()->visibleTo($user);
        $counts = (clone $scope())->selectRaw(
            'COUNT(*) as total, '
            .'SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as open_count, '
            .'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count, '
            .'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as progress_count, '
            .'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as resolved_count',
            ['Resolved', 'Pending', 'In Progress', 'Resolved'],
        )->first();

        return [
            'requests' => $requests,
            'total' => (int) $counts->total,
            'open' => (int) $counts->open_count,
            'pending' => (int) $counts->pending_count,
            'inProgress' => (int) $counts->progress_count,
            'resolved' => (int) $counts->resolved_count,
            'citizens' => $user->isAdmin()
                ? User::query()->where('role', 'citizen')->count()
                : null,
            'categories' => $user->isAdmin()
                ? (clone $scope())->selectRaw('category, COUNT(*) as total')->groupBy('category')->orderByDesc('total')->get()
                : collect(),
        ];
    }
}
