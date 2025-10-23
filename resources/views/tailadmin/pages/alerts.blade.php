@extends('tailadmin.layouts.app')

@section('title', 'Alerts - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            Alerts
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-brand-500">Alerts</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <!-- Default Alerts -->
        <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Default Alerts</h3>

            <div class="space-y-4">
                <!-- Success Alert -->
                <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <svg class="h-5 w-5 flex-shrink-0 fill-green-500" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-semibold text-green-800 dark:text-green-300">Success!</h4>
                        <p class="text-sm text-green-700 dark:text-green-400">Your action has been completed successfully.</p>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <svg class="h-5 w-5 flex-shrink-0 fill-blue-500" viewBox="0 0 20 20">
                        <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-semibold text-blue-800 dark:text-blue-300">Information</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-400">Here's some useful information you should know.</p>
                    </div>
                </div>

                <!-- Warning Alert -->
                <div class="flex items-center gap-3 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <svg class="h-5 w-5 flex-shrink-0 fill-yellow-500" viewBox="0 0 20 20">
                        <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-300">Warning!</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400">Please review this important warning message.</p>
                    </div>
                </div>

                <!-- Error Alert -->
                <div class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <svg class="h-5 w-5 flex-shrink-0 fill-red-500" viewBox="0 0 20 20">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-semibold text-red-800 dark:text-red-300">Error!</h4>
                        <p class="text-sm text-red-700 dark:text-red-400">An error occurred. Please try again later.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts with Actions -->
        <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Alerts with Actions</h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 fill-brand-500" viewBox="0 0 20 20">
                            <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                        </svg>
                        <p class="text-sm text-brand-700 dark:text-brand-400">You have a new message from admin.</p>
                    </div>
                    <div class="flex gap-2">
                        <button class="rounded bg-brand-500 px-3 py-1 text-sm text-white hover:bg-brand-600">View</button>
                        <button class="rounded bg-gray-200 px-3 py-1 text-sm text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
