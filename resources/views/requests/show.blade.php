@extends('layouts.app')

@section('title', $serviceRequest->request_number)
@section('breadcrumb', 'Request details')

@section('content')
<div class="page-heading">
    <div><a class="back-link" href="{{ $admin ? route('admin.requests.index') : route('requests.index') }}">← Back to {{ $admin ? 'all requests' : 'my requests' }}</a><h1>{{ $serviceRequest->request_number }}</h1><p>Submitted {{ $serviceRequest->created_at->format('F j, Y · g:i a') }}</p></div>
    <span class="status status-{{ str($serviceRequest->status)->slug() }}">{{ $serviceRequest->status }}</span>
</div>
<div class="detail-grid">
    <section class="panel detail-panel">
        <div class="detail-heading"><span class="eyebrow">{{ $serviceRequest->category }}</span><h2>{{ $serviceRequest->location }}</h2><p>{{ $serviceRequest->description }}</p></div>
        @if($serviceRequest->image_path)
            <a class="request-photo" href="{{ route('requests.image', $serviceRequest) }}" target="_blank" rel="noopener">
                <img src="{{ route('requests.image', $serviceRequest) }}" alt="Photo attached to request {{ $serviceRequest->request_number }}">
                <span>Open attached photo ↗</span>
            </a>
        @endif
        @if($admin)
            <div class="citizen-card"><span class="avatar">{{ mb_substr($serviceRequest->displayCitizenName(), 0, 1) }}</span><div><small>Submitted by</small><strong>{{ $serviceRequest->displayCitizenName() }}</strong></div></div>
        @endif
    </section>
    <aside class="panel timeline-panel">
        <div class="panel-head"><h2>Status history</h2></div>
        <ol class="timeline">
            @forelse($serviceRequest->statusUpdates as $update)
                <li class="timeline-item">
                    <span class="timeline-mark"></span>
                    <div><strong>{{ $update->to_status }}</strong><time datetime="{{ $update->created_at->toIso8601String() }}">{{ $update->created_at->format('M j, Y · g:i a') }}</time>
                        @if($update->note)<p>{{ $update->note }}</p>@endif
                        @if($admin && $update->changedBy)<small>Updated by {{ $update->changedBy->name }}</small>@endif
                    </div>
                </li>
            @empty
                <li class="timeline-item"><span class="timeline-mark"></span><div><strong>{{ $serviceRequest->status }}</strong><time>{{ $serviceRequest->created_at->format('M j, Y · g:i a') }}</time><p>Request submitted.</p></div></li>
            @endforelse
        </ol>
        @if($admin && $nextStatus)
            <form class="status-update-form" method="POST" action="{{ route('admin.requests.status', $serviceRequest) }}">
                @csrf @method('PATCH')
                @error('status')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                <h3>Move request forward</h3>
                <p>The next status is <strong>{{ $nextStatus }}</strong>.</p>
                <label class="field"><span>Update note <span class="muted">(optional)</span></span><textarea name="note" rows="3" maxlength="240" placeholder="Share a short update for the citizen">{{ old('note') }}</textarea>@error('note')<span class="field-error">{{ $message }}</span>@enderror</label>
                <input type="hidden" name="status" value="{{ $nextStatus }}">
                <button class="btn btn-primary" type="submit">Mark {{ strtolower($nextStatus) }}</button>
            </form>
        @endif
    </aside>
</div>
@endsection
