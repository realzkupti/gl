@extends('tailadmin.layouts.app')

@section('title', 'Buttons - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">Buttons</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li class="font-medium text-brand-500">Buttons</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <!-- Primary Buttons -->
        <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Primary Buttons</h3>
            <div class="flex flex-wrap gap-3">
                <button class="rounded bg-brand-500 px-6 py-2 text-white hover:bg-brand-600">Default</button>
                <button class="rounded bg-blue-500 px-6 py-2 text-white hover:bg-blue-600">Primary</button>
                <button class="rounded bg-green-500 px-6 py-2 text-white hover:bg-green-600">Success</button>
                <button class="rounded bg-yellow-500 px-6 py-2 text-white hover:bg-yellow-600">Warning</button>
                <button class="rounded bg-red-500 px-6 py-2 text-white hover:bg-red-600">Danger</button>
            </div>
        </div>

        <!-- Outline Buttons -->
        <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Outline Buttons</h3>
            <div class="flex flex-wrap gap-3">
                <button class="rounded border-2 border-brand-500 px-6 py-2 text-brand-500 hover:bg-brand-500 hover:text-white">Default</button>
                <button class="rounded border-2 border-blue-500 px-6 py-2 text-blue-500 hover:bg-blue-500 hover:text-white">Primary</button>
                <button class="rounded border-2 border-green-500 px-6 py-2 text-green-500 hover:bg-green-500 hover:text-white">Success</button>
                <button class="rounded border-2 border-yellow-500 px-6 py-2 text-yellow-500 hover:bg-yellow-500 hover:text-white">Warning</button>
                <button class="rounded border-2 border-red-500 px-6 py-2 text-red-500 hover:bg-red-500 hover:text-white">Danger</button>
            </div>
        </div>

        <!-- Button Sizes -->
        <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Button Sizes</h3>
            <div class="flex flex-wrap items-center gap-3">
                <button class="rounded bg-brand-500 px-3 py-1 text-sm text-white hover:bg-brand-600">Small</button>
                <button class="rounded bg-brand-500 px-6 py-2 text-white hover:bg-brand-600">Medium</button>
                <button class="rounded bg-brand-500 px-8 py-3 text-lg text-white hover:bg-brand-600">Large</button>
            </div>
        </div>

        <!-- Icon Buttons -->
        <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Icon Buttons</h3>
            <div class="flex flex-wrap gap-3">
                <button class="flex items-center gap-2 rounded bg-brand-500 px-6 py-2 text-white hover:bg-brand-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New
                </button>
                <button class="flex items-center gap-2 rounded bg-blue-500 px-6 py-2 text-white hover:bg-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit
                </button>
                <button class="flex items-center gap-2 rounded bg-red-500 px-6 py-2 text-white hover:bg-red-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
