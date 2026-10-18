@extends('layouts.app')

@section('title', $admin ? 'All requests' : 'My requests')
@section('breadcrumb', $admin ? 'All requests' : 'My requests')

@section('content')
<div class="page-heading"><div><h1>{{ $admin ? 'All service requests' : 'My requests' }}</h1><p>{{ $admin ? 'Review, search, and update requests submitted by your community.' : 'Track progress and see updates on the issues you have reported.' }}</p></div>@unless($admin)<a class="btn btn-primary" href="{{ route('requests.create') }}">＋ &nbsp; New request</a>@endunless</div>
<section class="panel">
    <form class="toolbar" method="GET" action="{{ $admin ? route('admin.requests.index') : route('requests.index') }}">
        <div class="search-wrap"><span aria-hidden="true">⌕</span><input name="search" type="search" value="{{ request('search') }}" placeholder="Search by ID, location, category..." aria-label="Search requests"></div>
        <select name="status" aria-label="Filter by status"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
        <select name="category" aria-label="Filter by category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select>
        <button class="btn btn-light" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'status', 'category']))<a class="btn btn-outline" href="{{ $admin ? route('admin.requests.index') : route('requests.index') }}">Clear</a>@endif
    </form>
    @if($requests->isNotEmpty())
        <div class="table-wrap"><table><thead><tr><th>Request</th>@if($admin)<th>Citizen</th>@endif<th>Category</th><th>Date submitted</th><th>Status</th>@if($admin)<th>Update status</th>@endif</tr></thead><tbody>
            @foreach($requests as $serviceRequest)
                <tr>
                    <td><div class="request-title">{{ $serviceRequest->request_number }}<span>{{ $serviceRequest->location }}</span></div></td>
                    @if($admin)<td>{{ $serviceRequest->displayCitizenName() }}</td>@endif
                    <td>{{ $serviceRequest->category }}</td>
                    <td>{{ $serviceRequest->created_at->format('M j, Y') }}</td>
                    <td><span class="status status-{{ str($serviceRequest->status)->slug() }}">{{ $serviceRequest->status }}</span></td>
                    @if($admin)
                        <td>
                            @if($next = match($serviceRequest->status){ 'Pending' => 'In Progress', 'In Progress' => 'Resolved', default => null })
                                <form method="POST" action="{{ route('admin.requests.status', $serviceRequest) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $next }}"><button class="btn btn-light btn-small" type="submit">Mark {{ strtolower($next) }}</button></form>
                            @else
                                <span class="muted">Complete</span>
                            @endif
                        </td>
                    @endif
                </tr>
                <tr class="description-row"><td colspan="{{ $admin ? 6 : 4 }}">{{ $serviceRequest->description }}@if($serviceRequest->image_path)<a class="text-link photo-link" href="{{ Storage::disk('public')->url($serviceRequest->image_path) }}" target="_blank" rel="noopener">View photo ↗</a>@endif</td></tr>
            @endforeach
        </tbody></table></div>
        <div class="pagination">{{ $requests->links() }}</div>
    @else
        <div class="table-empty"><strong>{{ request()->hasAny(['search', 'status', 'category']) ? 'No matching requests' : 'No requests yet' }}</strong>{{ request()->hasAny(['search', 'status', 'category']) ? 'Try changing your search or filters.' : 'Requests you submit will appear here.' }}@unless($admin)<br><a class="text-link" href="{{ route('requests.create') }}">Submit your first request</a>@endunless</div>
    @endif
</section>
@endsection
