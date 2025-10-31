@extends('tailadmin.layouts.app')

@section('title', 'Analytics - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            Analytics Dashboard
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('home') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-brand-500">Analytics</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5 mb-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['visitors']) }}
                    </h4>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Unique Visitors</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-green-50 dark:bg-green-900">
                    <svg class="fill-green-500" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 0C4.93 0 0 4.93 0 11s4.93 11 11 11 11-4.93 11-11S17.07 0 11 0zm0 20c-4.97 0-9-4.03-9-9s4.03-9 9-9 9 4.03 9 9-4.03 9-9 9z"/>
                        <path d="M11 5c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['pageViews']) }}
                    </h4>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Page Views</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900">
                    <svg class="fill-blue-500" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 2C6.03 2 2 6.03 2 11v8h2v-8c0-3.87 3.13-7 7-7s7 3.13 7 7v8h2v-8c0-4.97-4.03-9-9-9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-gray-900 dark:text-white">
                        {{ $stats['bounceRate'] }}%
                    </h4>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Bounce Rate</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900">
                    <svg class="fill-orange-500" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 13H5v-2h14v2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-title-md font-bold text-gray-900 dark:text-white">
                        {{ $stats['avgDuration'] }}
                    </h4>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Duration</span>
                </div>
                <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-900">
                    <svg class="fill-purple-500" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 2C6.48 2 2 6.48 2 11s4.48 9 9 9 9-4.48 9-9-4.48-9-9-9zm0 16c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                        <path d="M11.5 6H10v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-4 flex items-center justify-between">
            <h4 class="text-xl font-bold text-gray-900 dark:text-white">
                Traffic Overview
            </h4>
            <div class="flex gap-2">
                <button class="rounded bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">Week</button>
                <button class="rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">Month</button>
                <button class="rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">Year</button>
            </div>
        </div>

        <div class="flex h-[400px] items-end justify-center gap-2 p-4">
            @php
                $heights = [45, 52, 48, 65, 72, 58, 76, 80, 75, 70, 85, 90, 82, 78];
            @endphp
            @foreach($heights as $height)
                <div class="flex w-full flex-col items-center gap-1">
                    <div
                        class="w-full rounded-t bg-brand-500 transition-all hover:bg-brand-600 dark:bg-brand-400"
                        style="height: {{ $height }}%"
                    ></div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
