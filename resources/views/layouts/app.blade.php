<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @if (app()->environment('local'))
        <meta name="robots" content="noindex,nofollow">
    @endif
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="antialiased bg-gray-100">
    <div id="app">
        <nav class="bg-white shadow p-4">
            <div class="container mx-auto">
                <a href="/" class="font-bold">{{ config('app.name', 'Laravel') }}</a>
            </div>
        </nav>
        <main class="py-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
