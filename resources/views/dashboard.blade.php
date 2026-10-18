@extends('layouts.app')

@section('title', 'Overview')

@section('content')
@php
    $admin = auth()->user()->isAdmin();
    $chartTotal = max($total, 1);
    $pendingPercent = $pending / $chartTotal * 100;
    $progressPercent = $inProgress / $chartTotal * 100;
@endphp
<div class="page-heading">
    <div><h1>{{ $admin ? 'Good day, Administrator' : 'Hello, '.str(auth()->user()->name)->before(' ') }}</h1><p>{{ $admin ? 'Here’s what’s happening across your community today.' : 'Here’s an overview of the service requests you’ve submitted.' }}</p></div>
    @unless ($admin)<div class="page-actions"><a class="btn btn-primary" href="{{ route('requests.create') }}">＋ &nbsp; New request</a></div>@endunless
</div>
<section class="stats-grid">
    @php
        $stats = $admin
            ? [['Total requests', $total, 'Across all categories', '▣'], ['Open requests', $open, 'Awaiting completion', '◷'], ['Resolved', $resolved, 'Successfully completed', '✓'], ['Registered citizens', $citizens, 'Community accounts', '♧']]
            : [['My requests', $total, 'Submitted by you', '▣'], ['Open requests', $open, 'Awaiting completion', '◷'], ['Resolved', $resolved, 'Successfully completed', '✓'], ['In progress', $inProgress, 'Being worked on', '↗']];
    @endphp
    @foreach ($stats as [$label, $value, $note, $icon])
        <article class="stat-card"><div class="stat-top"><span>{{ $label }}</span><span class="stat-icon">{{ $icon }}</span></div><div class="stat-value">{{ $value }}</div><div class="stat-foot">{{ $note }}</div></article>
    @endforeach
</section>
<section class="dashboard-grid">
    <article class="panel">
        <div class="panel-head"><h2>{{ $admin ? 'Recent service requests' : 'Your recent requests' }}</h2><a href="{{ $admin ? route('admin.requests.index') : route('requests.index') }}">View all</a></div>
        @if ($requests->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Request</th>@if($admin)<th>Citizen</th>@endif<th>Category</th><th>Date submitted</th><th>Status</th></tr></thead><tbody>
                @foreach ($requests as $serviceRequest)
                    <tr><td><a class="request-title" href="{{ route('requests.show', $serviceRequest) }}">{{ $serviceRequest->request_number }}<span>{{ $serviceRequest->location }}</span></a></td>@if($admin)<td>{{ $serviceRequest->displayCitizenName() }}</td>@endif<td>{{ $serviceRequest->category }}</td><td>{{ $serviceRequest->created_at->format('M j, Y') }}</td><td><span class="status status-{{ str($serviceRequest->status)->slug() }}">{{ $serviceRequest->status }}</span></td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="table-empty"><strong>No requests yet</strong>@unless($admin)Submit your first request to see it here.<br><a class="text-link" href="{{ route('requests.create') }}">Submit a request</a>@endunless</div>
        @endif
    </article>
    <article class="panel chart-panel">
        <div class="chart-title"><div><h2>Case status</h2><p>{{ $admin ? 'Community-wide request breakdown' : 'Your requests by current status' }}</p></div><span class="chart-total">{{ $total }} total</span></div>
        <div class="donut-wrap"><div class="donut" style="--pending:{{ $pendingPercent }}%;--progress:{{ $pendingPercent + $progressPercent }}%"><div class="donut-center"><strong>{{ $total }}</strong><span>Total cases</span></div></div></div>
        <div class="legend"><div class="legend-row"><i class="legend-dot"></i> Pending <strong>{{ $pending }}</strong></div><div class="legend-row"><i class="legend-dot blue"></i> In progress <strong>{{ $inProgress }}</strong></div><div class="legend-row"><i class="legend-dot green"></i> Resolved <strong>{{ $resolved }}</strong></div></div>
    </article>
</section>
@endsection
