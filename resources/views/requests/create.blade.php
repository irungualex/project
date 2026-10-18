@extends('layouts.app')

@section('title', 'Submit a request')
@section('breadcrumb', 'Submit a request')

@section('content')
<div class="page-heading"><div><h1>Submit a service request</h1><p>Tell us what needs attention and we’ll make sure it reaches the right team.</p></div></div>
<div class="submit-layout">
    <section class="panel form-panel">
        <h2>Request details</h2><p>Fields marked with * are required. Please share enough detail to help the service team locate and address the issue.</p>
        <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field"><label for="category">Service category *</label><select id="category" name="category" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>@endforeach</select>@error('category')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="location">Location *</label><input id="location" name="location" value="{{ old('location') }}" placeholder="Street, neighborhood, or nearby landmark" maxlength="160" required>@error('location')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="description">Describe the issue *</label><textarea id="description" name="description" placeholder="What happened? When did you notice it? Include any details that could help." minlength="10" maxlength="1200" required>{{ old('description') }}</textarea>@error('description')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="image">Photo (optional)</label><div class="upload-box"><span class="upload-icon">＋</span><span class="upload-copy"><strong>Add a photo</strong>JPEG, PNG, or WebP · up to 1.5 MB</span><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"></div>@error('image')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="btn btn-primary" type="submit">Submit request &nbsp; →</button>
        </form>
    </section>
    <aside class="panel info-panel"><h3>What happens next?</h3>
        <div class="process-step"><span class="step-number">1</span><div><strong>We review your request</strong><p>Your request is sent to the appropriate public service team.</p></div></div>
        <div class="process-step"><span class="step-number">2</span><div><strong>Work gets underway</strong><p>Track when the team marks your request as in progress.</p></div></div>
        <div class="process-step"><span class="step-number">3</span><div><strong>Issue resolved</strong><p>Check back here to see when the issue is resolved.</p></div></div>
        <div class="info-tip">Please do not include sensitive personal information in your request description.</div>
    </aside>
</div>
@endsection
