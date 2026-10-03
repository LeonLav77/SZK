@props(['title' => 'Admin'])

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Zlatna karta</title>
    <link rel="stylesheet" href="{{ asset('admin.css') }}">
    @stack('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
<body>
    @auth
        <header class="topbar">
            <a href="{{ route('admin.dashboard') }}" class="brand">Zlatna karta · admin</a>
            <nav>
                <a href="{{ route('admin.editions.index') }}" @class(['on' => request()->routeIs('admin.editions.*', 'admin.coupons.*')])>Izdanja i kuponi</a>
                <a href="{{ route('admin.titulars.index') }}" @class(['on' => request()->routeIs('admin.titulars.*')])>Titulari</a>
                <a href="{{ route('admin.categories.index') }}" @class(['on' => request()->routeIs('admin.categories.*')])>Kategorije</a>
                <a href="{{ route('admin.users.index') }}" @class(['on' => request()->routeIs('admin.users.*')])>Korisnici</a>
                <a href="{{ route('admin.settings.edit') }}" @class(['on' => request()->routeIs('admin.settings.*')])>Postavke</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="link">Odjava</button>
            </form>
        </header>
    @endauth

    <main>
        @if (session('success'))
            <div class="flash success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash error">Nešto nije u redu. Provjeri polja označena crveno.</div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
