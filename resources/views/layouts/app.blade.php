<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cases') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @auth
        <header class="topbar">
            <a class="brand" href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
            <nav>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('cases.index') }}">Cases</a>
            </nav>
            <div class="user">
                <span>{{ auth()->user()->name }} ({{ auth()->user()->role?->label() ?? 'User' }})</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-link">Log out</button>
                </form>
            </div>
        </header>
    @endauth

    <main class="container">
        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
