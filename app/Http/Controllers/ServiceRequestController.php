<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceRequestController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.requests.index');
        }

        return $this->listing($request, false);
    }

    public function adminIndex(Request $request): View
    {
        return $this->listing($request, true);
    }

    public function create(): View
    {
        abort_if(request()->user()->isAdmin(), 403);

        return view('requests.create', [
            'categories' => ServiceRequest::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'category' => ['required', Rule::in(ServiceRequest::CATEGORIES)],
            'location' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:1200'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1536'],
        ]);

        $serviceRequest = $request->user()->serviceRequests()->create([
            'citizen_name' => $request->user()->name,
            'category' => $validated['category'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'image_path' => $request->file('image')?->store('service-requests', 'public'),
            'status' => 'Pending',
        ]);

        $serviceRequest->update([
            'request_number' => 'CD-'.str_pad((string) $serviceRequest->id, 6, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('requests.index')->with('status', "Request {$serviceRequest->request_number} submitted successfully.");
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ServiceRequest::STATUSES)],
        ]);

        $nextStatus = match ($serviceRequest->status) {
            'Pending' => 'In Progress',
            'In Progress' => 'Resolved',
            default => null,
        };

        if ($validated['status'] !== $nextStatus) {
            throw ValidationException::withMessages([
                'status' => 'Requests can only move forward one status at a time.',
            ]);
        }

        $serviceRequest->update(['status' => $validated['status']]);

        return back()->with('status', "{$serviceRequest->request_number} updated to {$serviceRequest->status}.");
    }

    private function listing(Request $request, bool $admin): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(ServiceRequest::STATUSES)],
            'category' => ['nullable', Rule::in(ServiceRequest::CATEGORIES)],
        ]);

        $query = ServiceRequest::query()
            ->visibleTo($request->user())
            ->with('citizen:id,name')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('request_number', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('citizen_name', 'like', "%{$search}%")
                        ->orWhereHas('citizen', fn ($citizen) => $citizen->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->latest();

        return view('requests.index', [
            'requests' => $query->paginate(12)->withQueryString(),
            'categories' => ServiceRequest::CATEGORIES,
            'statuses' => ServiceRequest::STATUSES,
            'admin' => $admin,
        ]);
    }
}
