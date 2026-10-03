<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Studentska zlatna karta</title>
    <meta name="theme-color" content="#0F009C">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('app.css') }}?v={{ filemtime(public_path('app.css')) }}">
</head>
<body>
    <header class="bar">
        <div class="bar-in">
            <a class="brand" href="#/">Studentska zlatna karta</a>
            <nav id="nav" aria-label="Glavna navigacija"></nav>
        </div>
    </header>
    <main id="app" class="page"></main>

    <script src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}"></script>
</body>
</html>
