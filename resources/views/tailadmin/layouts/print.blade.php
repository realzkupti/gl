<!doctype html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', config('app.name') . ' - Print')</title>

    <!-- TailAdmin CSS -->
    <link rel="icon" href="{{ asset('tailadmin-assets/images/favicon.ico') }}">
    <link href="{{ asset('tailadmin/style.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body
    x-data="{
        page: '{{ $page ?? 'print' }}',
        loaded: true,
        darkMode: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
>
    <!-- ===== Preloader Start ===== -->
    <div
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start (No Sidebar) ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- ===== Simple Header Start ===== -->
            <header class="sticky top-0 z-999 flex w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
                <div class="flex flex-grow items-center justify-between py-4 px-4 shadow-sm md:px-6 2xl:px-11">
                    <div class="flex items-center gap-2 sm:gap-4">
                        <!-- Logo -->
                        <a class="block flex-shrink-0" href="{{ route('tailadmin.dashboard') }}">
                            <span class="text-xl font-bold text-brand-500">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-3 2xl:gap-7">
                        <!-- Dark Mode Toggle -->
                        <div class="flex items-center gap-2">
                            <label class="relative cursor-pointer">
                                <input type="checkbox" @click="darkMode = !darkMode" class="sr-only" />
                                <span class="text-gray-700 dark:text-gray-300">
                                    <svg x-show="!darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    <svg x-show="darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </span>
                            </label>
                        </div>

                        <!-- Back Button -->
                        <a href="{{ route('tailadmin.dashboard') }}" class="flex items-center gap-2 rounded bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="hidden sm:inline">กลับหน้าหลัก</span>
                        </a>
                    </div>
                </div>
            </header>
            <!-- ===== Simple Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main class="flex-grow bg-gray-50 dark:bg-gray-800">
                @yield('content')
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    <!-- TailAdmin Bundle (includes Alpine.js) -->
    <script src="{{ asset('tailadmin/bundle.js') }}"></script>

    @stack('scripts')
</body>
</html>
