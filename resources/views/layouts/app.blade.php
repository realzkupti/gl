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
    @stack('styles')
    <style>
        /* Simple Dark Mode Overrides */
        html.dark, body.dark { background-color: #0f172a; color: #e5e7eb; }
        body.dark .bg-white { background-color: #111827 !important; color: #e5e7eb; }
        body.dark .bg-gray-100 { background-color: #0b1220 !important; }
        body.dark .border { border-color: #374151 !important; }
        body.dark a { color: #93c5fd; }
        body.dark table thead tr { background-color: #111827 !important; }
        body.dark table tbody tr { background-color: #0f172a; }
        body.dark .modal-scroll { background-color: #111827 !important; color: #e5e7eb; }
        body.dark .sticky-note .sn-wrap { background: #1f2937; border-color: #374151; }
        body.dark .sticky-note .sn-head { background: #374151; color: #f1f5f9; }
        body.dark .sticky-note textarea { color: #f1f5f9; }
    </style>
</head>
<body class="antialiased bg-gray-100" id="app-body">
    <div id="app">
        <nav class="bg-white shadow p-4">
            <div class="container mx-auto">
                <div class="flex items-center justify-between">
                    <a href="/" class="font-bold">{{ config('app.name', 'Laravel') }}</a>
                    <div class="flex items-center gap-3">
                        <button id="theme-toggle" class="px-2 py-1 rounded border text-sm" title="Toggle Dark Mode">Dark</button>
                    </div>
                </div>
            </div>
        </nav>
        <main class="py-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        (function(){
            var key = 'gl_theme';
            function applyTheme(t){
                var body = document.getElementById('app-body');
                if (!body) return;
                if (t === 'dark') { body.classList.add('dark'); document.documentElement.classList.add('dark'); }
                else { body.classList.remove('dark'); document.documentElement.classList.remove('dark'); }
                try { localStorage.setItem(key, t); } catch(e){}
                var btn = document.getElementById('theme-toggle');
                if (btn) btn.textContent = (t==='dark' ? 'Light' : 'Dark');
            }
            var saved = null; try { saved = localStorage.getItem(key); } catch(e){}
            if (saved) applyTheme(saved);
            var btn = document.getElementById('theme-toggle');
            if (btn) btn.addEventListener('click', function(){
                var isDark = document.getElementById('app-body').classList.contains('dark');
                applyTheme(isDark ? 'light' : 'dark');
            });
        })();
    </script>
</body>
</html>
