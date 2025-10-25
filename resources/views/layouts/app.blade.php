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
    @php
        $hasViteBuild = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
    @endphp
    @if ($hasViteBuild)
        @vite(['resources/css/app.css','resources/js/app.js'])
    @else
        <!-- Temporary fallback so the app doesn't crash before Vite build is available -->
        <link rel="stylesheet" href="{{ asset('tailadmin/style.css') }}">
    @endif
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
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
        /* Sticky notes keep their own colors regardless of theme */
        /* Form controls in dark mode */
        body.dark input[type="text"],
        body.dark input[type="search"],
        body.dark input[type="number"],
        body.dark input[type="date"],
        body.dark select,
        body.dark textarea {
            background-color: #0b1220 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }
        body.dark ::placeholder { color: #9ca3af !important; }
    </style>
</head>
<body class="antialiased bg-gray-100" id="app-body">
    <div id="app">
        <nav class="bg-white shadow p-4">
            <div class="container mx-auto">
                <div class="flex items-center justify-between">
                    <a href="/" class="font-bold">{{ config('app.name', 'Laravel') }}</a>
                    <div class="flex items-center gap-3">
                        <a href="/" class="px-2 py-1 rounded border text-sm">กลับเมนู</a>
                        <button id="theme-toggle" class="px-2 py-1 rounded border text-sm" title="Toggle Dark Mode">Dark</button>
                    </div>
                </div>
            </div>
        </nav>
        <main class="py-6">
            @if (session('status'))
                <div class="container mx-auto max-w-3xl mb-4">
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                        {{ session('status') }}
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="container mx-auto max-w-3xl mb-4">
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif
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
        // Global helpers for alerts
        window.showLoading = function(title){
            if (!window.Swal) return;
            Swal.fire({ title: title || 'กำลังโหลด...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        }
        window.showError = function(message){
            if (!window.Swal) return;
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: message || 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้', confirmButtonText: 'ปิด' });
        }
    </script>
</body>
</html>
