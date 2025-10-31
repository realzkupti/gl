@extends('tailadmin.layouts.app')

@section('title', 'Tables - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">Tables</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li class="font-medium text-brand-500">Tables</li>
            </ol>
        </nav>
    </div>

    <!-- Basic Table -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-7.5 py-4 dark:border-gray-800">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Users Table</h3>
        </div>

        <div class="p-7.5">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">ID</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Name</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Email</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Role</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Status</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-4 py-4 text-gray-900 dark:text-white">{{ $user['id'] }}</td>
                            <td class="px-4 py-4 text-gray-900 dark:text-white">{{ $user['name'] }}</td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-400">{{ $user['email'] }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $user['role'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if($user['status'] === 'active')
                                    <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-sm font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex gap-2">
                                    <button class="text-blue-500 hover:text-blue-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <button class="text-red-500 hover:text-red-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Showing 1 to {{ count($users) }} of {{ count($users) }} results
                </p>
                <div class="flex gap-2">
                    <button class="rounded border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Previous
                    </button>
                    <button class="rounded bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">1</button>
                    <button class="rounded border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        2
                    </button>
                    <button class="rounded border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
