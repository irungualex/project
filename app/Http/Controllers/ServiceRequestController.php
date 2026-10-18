<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdvanceServiceRequestStatus;
use App\Http\Requests\FilterServiceRequests;
use App\Http\Requests\StoreServiceRequest as StoreServiceRequestForm;
use App\Models\ServiceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceRequestController extends Controller
{
    public function index(FilterServiceRequests $request): View|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.requests.index');
        }

        return $this->listing($request, false);
    }

    public function adminIndex(FilterServiceRequests $request): View
    {
        return $this->listing($request, true);
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        $this->authorize('view', $serviceRequest);

        return view('requests.show', [
            'serviceRequest' => $serviceRequest->load([
                'citizen:id,name',
                'statusUpdates.changedBy:id,name',
            ]),
            'admin' => request()->user()->isAdmin(),
            'nextStatus' => $this->nextStatus($serviceRequest->status),
        ]);
    }

    public function image(ServiceRequest $serviceRequest): StreamedResponse
    {
        $this->authorize('view', $serviceRequest);
        abort_unless($serviceRequest->image_path, 404);

        return Storage::disk(config('filesystems.default'))->response(
            $serviceRequest->image_path,
            basename($serviceRequest->image_path),
            ['Cache-Control' => 'private, no-store'],
        );
    }

    public function create(): View
    {
        return view('requests.create', ['categories' => ServiceRequest::CATEGORIES]);
    }

    public function store(StoreServiceRequestForm $request): RedirectResponse
    {
        $validated = $request->validated();

        $serviceRequest = DB::transaction(function () use ($request, $validated): ServiceRequest {
            $serviceRequest = $request->user()->serviceRequests()->create([
                'citizen_name' => $request->user()->name,
                'category' => $validated['category'],
                'location' => $validated['location'],
                'description' => $validated['description'],
                'image_path' => $request->file('image')?->store('service-requests', config('filesystems.default')),
                'status' => 'Pending',
            ]);

            $serviceRequest->update([
                'request_number' => 'CD-'.str_pad((string) $serviceRequest->id, 6, '0', STR_PAD_LEFT),
            ]);

            $serviceRequest->statusUpdates()->create([
                'to_status' => 'Pending',
                'changed_by' => $request->user()->id,
                'note' => 'Request submitted.',
            ]);

            return $serviceRequest;
        });

        return redirect()->route('requests.show', $serviceRequest)
            ->with('status', "Request {$serviceRequest->request_number} submitted successfully.");
    }

    public function updateStatus(AdvanceServiceRequestStatus $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $validated = $request->validated();
        DB::transaction(function () use ($request, $serviceRequest, $validated): void {
            $lockedRequest = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            $nextStatus = $this->nextStatus($lockedRequest->status);

            if ($validated['status'] !== $nextStatus) {
                throw ValidationException::withMessages([
                    'status' => 'Requests can only move forward one status at a time.',
                ]);
            }

            $previousStatus = $lockedRequest->status;
            $lockedRequest->update(['status' => $validated['status']]);
            $lockedRequest->statusUpdates()->create([
                'from_status' => $previousStatus,
                'to_status' => $validated['status'],
                'changed_by' => $request->user()->id,
                'note' => $validated['note'] ?? null,
            ]);
        });

        return back()->with('status', "{$serviceRequest->request_number} updated to {$validated['status']}.");
    }

    private function listing(FilterServiceRequests $request, bool $admin): View
    {
        $filters = $request->validated();
        $query = ServiceRequest::query()
            ->visibleTo($request->user())
            ->with('citizen:id,name')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('request_number', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('citizen_name', 'like', "%{$search}%")
                        ->orWhereHas('citizen', fn (Builder $citizen) => $citizen->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->latest();

        return view('requests.index', [
            'requests' => $query->paginate(12)->withQueryString(),
            'categories' => ServiceRequest::CATEGORIES,
            'statuses' => ServiceRequest::STATUSES,
            'admin' => $admin,
        ]);
    }

    private function nextStatus(string $status): ?string
    {
        return match ($status) {
            'Pending' => 'In Progress',
            'In Progress' => 'Resolved',
            default => null,
        };
    }
}
