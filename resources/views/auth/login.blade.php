@extends('layouts.app')

@section('title', 'Sign in')

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
            <h2>Welcome back</h2>
            <p class="auth-intro">Sign in to report an issue or check on a request.</p>
            <div class="auth-tabs"><a class="active" href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}">Create account</a></div>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="you@example.com" required autofocus></div>
                <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required></div>
                <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                <button class="btn btn-primary btn-full" type="submit">Sign in to CivicDesk</button>
            </form>
            @if ($errors->any())
                <div class="validation-errors" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            @endif
        </div>
    </section>
</div>
@endsection
