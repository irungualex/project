@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('breadcrumb', 'Admin overview')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">CivicDesk administration</span>
        <h1>Service overview</h1>
        <p>Monitor community reports and follow cases that need attention.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="{{ route('admin.requests.index') }}">Manage all requests</a>
    </div>
</div>

<section class="stats-grid" aria-label="Service request statistics">
    <article class="stat-card"><div class="stat-top"><span>Total requests</span><span class="stat-icon">▣</span></div><div class="stat-value">{{ $total }}</div><div class="stat-foot">All submitted service cases</div></article>
    <article class="stat-card"><div class="stat-top"><span>Open cases</span><span class="stat-icon">◷</span></div><div class="stat-value">{{ $open }}</div><div class="stat-foot">Pending or currently in progress</div></article>
    <article class="stat-card"><div class="stat-top"><span>Resolved</span><span class="stat-icon">✓</span></div><div class="stat-value">{{ $resolved }}</div><div class="stat-foot">Cases marked as completed</div></article>
    <article class="stat-card"><div class="stat-top"><span>Citizens</span><span class="stat-icon">♧</span></div><div class="stat-value">{{ $citizens }}</div><div class="stat-foot">Registered community accounts</div></article>
</section>

<section class="admin-insights-grid">
    <article class="panel admin-insights-panel">
        <div class="panel-head"><div><h2>Request pipeline</h2><p class="admin-panel-note">Current status across all service cases</p></div><a href="{{ route('admin.requests.index') }}">Review queue</a></div>
        <div class="admin-status-list">
            <div class="admin-status-row"><span class="status status-pending">Pending</span><div class="admin-meter"><span class="meter-pending" style="width: {{ $total ? ($pending / $total) * 100 : 0 }}%"></span></div><strong>{{ $pending }}</strong></div>
            <div class="admin-status-row"><span class="status status-in-progress">In Progress</span><div class="admin-meter"><span class="meter-progress" style="width: {{ $total ? ($inProgress / $total) * 100 : 0 }}%"></span></div><strong>{{ $inProgress }}</strong></div>
            <div class="admin-status-row"><span class="status status-resolved">Resolved</span><div class="admin-meter"><span class="meter-resolved" style="width: {{ $total ? ($resolved / $total) * 100 : 0 }}%"></span></div><strong>{{ $resolved }}</strong></div>
        </div>
    </article>

    <article class="panel admin-insights-panel">
        <div class="panel-head"><div><h2>Requests by category</h2><p class="admin-panel-note">Where residents need support</p></div></div>
        @if ($categories->isNotEmpty())
            @php($largestCategory = max((int) $categories->max('total'), 1))
            <div class="admin-category-list">
                @foreach ($categories as $category)
                    <div class="admin-category-row"><div class="admin-category-label"><span>{{ $category->category }}</span><strong>{{ $category->total }}</strong></div><div class="admin-meter"><span style="width: {{ ($category->total / $largestCategory) * 100 }}%"></span></div></div>
                @endforeach
            </div>
        @else
            <div class="table-empty"><strong>No service requests yet</strong>Category analytics will appear when residents submit cases.</div>
        @endif
    </article>
</section>

<article class="panel admin-recent-panel">
    <div class="panel-head"><div><h2>Recently submitted</h2><p class="admin-panel-note">Latest reports from the community</p></div><a href="{{ route('admin.requests.index') }}">View all requests</a></div>
    @if ($requests->isNotEmpty())
        <div class="table-wrap"><table><thead><tr><th>Case</th><th>Citizen</th><th>Category</th><th>Location</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody>
            @foreach ($requests as $serviceRequest)
                <tr>
                    <td><a class="request-title" href="{{ route('requests.show', $serviceRequest) }}">{{ $serviceRequest->request_number }}</a></td>
                    <td>{{ $serviceRequest->displayCitizenName() }}</td>
                    <td>{{ $serviceRequest->category }}</td>
                    <td>{{ $serviceRequest->location }}</td>
                    <td>{{ $serviceRequest->created_at->format('M j, Y') }}</td>
                    <td><span class="status status-{{ str($serviceRequest->status)->slug() }}">{{ $serviceRequest->status }}</span></td>
                    <td><a class="text-link" href="{{ route('requests.show', $serviceRequest) }}">Open</a></td>
                </tr>
            @endforeach
        </tbody></table></div>
    @else
        <div class="table-empty"><strong>No requests to review</strong>New citizen submissions will appear here.</div>
    @endif
</article>
@endsection
