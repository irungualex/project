@extends('layouts.app')

@section('title', 'Public services, connected')

@section('content')
<div class="landing">
    <header class="landing-nav">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">C</span><span>CivicDesk</span></a>
        <nav aria-label="Account navigation">
            <a class="nav-login" href="{{ route('login') }}">Sign in</a>
            <a class="btn btn-primary" href="{{ route('register') }}">Create account</a>
        </nav>
    </header>
    <section class="landing-hero">
        <div class="landing-copy">
            <span class="eyebrow">Your community, heard</span>
            <h1>Small reports can make a big difference.</h1>
            <p>Report local service issues, follow progress, and help your community get the attention it deserves.</p>
            <div class="landing-actions">
                <a class="btn btn-primary" href="{{ route('register') }}">Report an issue <span aria-hidden="true">→</span></a>
                <a class="btn btn-outline" href="{{ route('login') }}">Track a request</a>
            </div>
            <p class="landing-note">For emergencies or immediate danger, contact local emergency services.</p>
        </div>
        <div class="landing-visual" aria-label="Service request progress">
            <div class="visual-orbit orbit-one"></div><div class="visual-orbit orbit-two"></div>
            <article class="sample-card">
                <div class="sample-top"><span class="sample-icon">✳</span><span class="status status-in-progress">In Progress</span></div>
                <span class="sample-label">COMMUNITY REQUEST · CD-004281</span>
                <h2>Streetlight out on Riverside Lane</h2>
                <p>Riverside, near the community library</p>
                <div class="sample-progress"><span></span></div>
                <div class="sample-steps"><span class="done">Submitted</span><span class="current">In progress</span><span>Resolved</span></div>
            </article>
            <div class="floating-note"><span class="floating-check">✓</span><span><strong>Every update matters</strong><small>Follow the status from start to finish</small></span></div>
        </div>
    </section>
    <section class="landing-features" aria-label="How CivicDesk helps">
        <article><span class="feature-number">01</span><h3>Tell us what needs attention</h3><p>Choose a service area and give your team the location and details they need.</p></article>
        <article><span class="feature-number">02</span><h3>Keep track of progress</h3><p>See each status update and follow your request through to resolution.</p></article>
        <article><span class="feature-number">03</span><h3>Help improve your community</h3><p>Clear reports help public service teams understand what needs to be done.</p></article>
    </section>
</div>
@endsection
