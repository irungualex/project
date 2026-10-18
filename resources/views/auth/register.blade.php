@extends('layouts.app')

@section('title', 'Create account')

@section('content')
<div class="auth-shell">
    <section class="auth-story">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">C</span><span>CivicDesk</span></a>
        <div class="story-copy">
            <span class="eyebrow">Your community, heard</span>
            <h1>Better neighborhoods start with you.</h1>
            <p>Report local issues, follow every update, and help make public services work better for everyone.</p>
            <div class="story-points"><span>✓ &nbsp; Simple issue reporting</span><span>✓ &nbsp; Updates you can follow</span></div>
        </div>
        <div class="story-footer">A clearer connection between citizens and public services.</div>
    </section>
    <section class="auth-panel">
        <div class="auth-card">
            <h2>Create your account</h2>
            <p class="auth-intro">Join your community and make your voice count.</p>
            <div class="auth-tabs"><a href="{{ route('login') }}">Sign in</a><a class="active" href="{{ route('register') }}">Create account</a></div>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="e.g. Amina Mwangi" required minlength="2" maxlength="80"></div>
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="new-password" placeholder="At least 8 characters" required minlength="8"></div>
                <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Enter your password again" required></div>
                <button class="btn btn-primary btn-full" type="submit">Create account</button>
            </form>
            @if ($errors->any())
                <div class="validation-errors" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif
        </div>
    </section>
</div>
@endsection
