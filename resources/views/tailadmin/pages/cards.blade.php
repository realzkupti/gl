@extends('tailadmin.layouts.app')

@section('title', 'Cards - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">Cards</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li class="font-medium text-brand-500">Cards</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Basic Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Basic Card</h3>
            <p class="text-gray-600 dark:text-gray-400">
                This is a simple card with basic styling and content.
            </p>
        </div>

        <!-- Card with Icon -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900">
                <svg class="h-6 w-6 fill-brand-500" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Card with Icon</h3>
            <p class="text-gray-600 dark:text-gray-400">
                Card featuring an icon at the top for visual emphasis.
            </p>
        </div>

        <!-- Stats Card -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">Total Revenue</div>
            <div class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">฿45,200</div>
            <div class="flex items-center gap-1 text-sm text-green-500">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"/>
                </svg>
                +12.5% from last month
            </div>
        </div>

        <!-- Card with Badge -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Project Status</h3>
                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                    Active
                </span>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Current project is on track and progressing well.
            </p>
        </div>

        <!-- Card with Button -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Card with Action</h3>
            <p class="mb-4 text-gray-600 dark:text-gray-400">
                This card includes an action button for user interaction.
            </p>
            <button class="rounded bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Learn More
            </button>
        </div>

        <!-- Gradient Card -->
        <div class="rounded-lg bg-gradient-to-br from-brand-500 to-purple-600 p-6 text-white shadow-lg">
            <h3 class="mb-2 text-lg font-semibold">Gradient Card</h3>
            <p class="mb-4 text-white/90">
                A beautiful card with gradient background.
            </p>
            <button class="rounded bg-white px-4 py-2 text-sm text-brand-500 hover:bg-gray-100">
                Get Started
            </button>
        </div>
    </div>
</div>
@endsection
