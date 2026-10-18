<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f5f7f4">
    <title>@yield('title', 'CivicDesk') — {{ config('app.name', 'CivicDesk') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@auth
    @php($admin = auth()->user()->isAdmin())
    <div class="workspace">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">⌂</span><span>CivicDesk</span></a>
            <div class="side-label">Workspace</div>
            <nav class="nav-list" aria-label="Main navigation">
                <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">▦</span><span>Overview</span></a>
                @if ($admin)
                    <a class="nav-item {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}" href="{{ route('admin.requests.index') }}"><span class="nav-icon">☷</span><span>All requests</span><span class="nav-badge">{{ \App\Models\ServiceRequest::count() }}</span></a>
                @else
                    <a class="nav-item {{ request()->routeIs('requests.index') ? 'active' : '' }}" href="{{ route('requests.index') }}"><span class="nav-icon">☷</span><span>My requests</span><span class="nav-badge">{{ auth()->user()->serviceRequests()->count() }}</span></a>
                    <a class="nav-item {{ request()->routeIs('requests.create') ? 'active' : '' }}" href="{{ route('requests.create') }}"><span class="nav-icon">＋</span><span>Submit a request</span></a>
                @endif
            </nav>
            <div class="sidebar-bottom">
                @unless ($admin)
                    <div class="help-card"><strong>Need urgent help?</strong><p>For emergencies or immediate danger, contact your local emergency services directly.</p></div>
                @endunless
                <div class="user-mini">
                    <span class="avatar">{{ collect(explode(' ', auth()->user()->name))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                    <span class="user-mini-text"><strong>{{ auth()->user()->name }}</strong><span>{{ $admin ? 'Administrator' : 'Community member' }}</span></span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Log out</button></form>
                </div>
            </div>
        </aside>
        <section class="main-area">
            <header class="topbar">
                <div class="breadcrumb"><button class="mobile-menu" id="mobile-menu" type="button" aria-label="Toggle navigation">☰</button> CivicDesk <span>/</span> <strong>@yield('breadcrumb', 'Overview')</strong></div>
                <div class="topbar-right"><span class="topbar-date">{{ now()->format('D, M j') }}</span><span class="role-pill">{{ $admin ? 'ADMIN PORTAL' : 'CITIZEN PORTAL' }}</span></div>
            </header>
            <main class="content">
                @if (session('status'))
                    <div class="flash" role="status">{{ session('status') }}</div>
                @endif
                @yield('content')
            </main>
        </section>
    </div>
@else
    <main>@yield('content')</main>
@endauth
<script>
    document.getElementById('mobile-menu')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.toggle('open');
    });
</script>
</body>
</html>
