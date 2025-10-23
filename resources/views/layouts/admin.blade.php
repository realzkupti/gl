@php
    $hasViteBuild = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
@endphp
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} — Admin</title>
    @if ($hasViteBuild)
        @vite(['resources/css/app.css','resources/js/app.js'])
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    @endif
    <!-- TailAdmin CSS (served from template folder) -->
    <link rel="stylesheet" href="/admin-assets/tailadmin.css">
    <link rel="stylesheet" href="/admin-assets/prism.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100" id="app-body">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 hidden md:block">
            <div class="p-4 font-bold text-lg">เมนูระบบ</div>
            <nav class="px-2 space-y-1">
                <a class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" href="/">หน้าหลัก</a>
                <a class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('admin.dashboard.demo') }}">Dashboard Demo</a>
                <a class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('trial-balance.plain') }}">งบทดลอง (ธรรมดา)</a>
                <a class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('trial-balance.branch') }}">งบทดลอง (แยกสาขา)</a>
                <a class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('admin.cheque') }}">ระบบเช็ค</a>
                <a class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('admin.users') }}">ผู้ใช้และสิทธิ</a>
            </nav>
        </aside>

        <!-- Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                    <div class="font-semibold">{{ config('app.name', 'Laravel') }} — Admin</div>
                    <div class="flex items-center gap-2">
                        <a href="/" class="px-2 py-1 border rounded text-sm">กลับเมนู</a>
                        <button id="theme-toggle" class="px-2 py-1 border rounded text-sm">Dark</button>
                    </div>
                </div>
            </header>
            <main class="flex-1 p-4 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    <script>
        (function(){
            var key='gl_theme';
            function applyTheme(t){
                var body=document.getElementById('app-body');
                if(!body) return;
                if(t==='dark'){ body.classList.add('dark'); document.documentElement.classList.add('dark'); }
                else { body.classList.remove('dark'); document.documentElement.classList.remove('dark'); }
                try{ localStorage.setItem(key,t); }catch(e){}
                var btn=document.getElementById('theme-toggle'); if(btn) btn.textContent=(t==='dark'?'Light':'Dark');
            }
            var saved=null; try{ saved=localStorage.getItem(key); }catch(e){}
            if(saved) applyTheme(saved);
            var btn=document.getElementById('theme-toggle'); if(btn) btn.addEventListener('click', function(){ var isDark=document.getElementById('app-body').classList.contains('dark'); applyTheme(isDark?'light':'dark'); });
        })();
    </script>
</body>
</html>
