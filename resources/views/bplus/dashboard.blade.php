@extends('tailadmin.layouts.app')

@section('title', 'Bplus Dashboard - ' . config('app.name'))

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Welcome Header -->
    <div class="mb-6">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            ยินดีต้อนรับสู่ระบบ Bplus
        </h2>
        @if(isset($company))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            บริษัทปัจจุบัน: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $company->label }}</span>
        </p>
        @else
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            กรุณาเลือกบริษัทเพื่อเริ่มใช้งาน
        </p>
        @endif
    </div>

    @if(isset($company))
    <!-- Company Info Card -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-start gap-4">
            @if($company->logo)
                <img
                    src="{{ asset('storage/' . $company->logo) }}"
                    alt="{{ $company->label }}"
                    class="w-16 h-16 object-contain rounded border border-gray-200 dark:border-gray-700 p-2"
                />
            @else
                <div class="w-16 h-16 rounded border border-gray-200 dark:border-gray-700 bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            @endif
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $company->label }}</h3>
                <div class="mt-2 space-y-1 text-sm text-gray-500 dark:text-gray-400">
                    <p><span class="font-medium">Key:</span> {{ $company->key }}</p>
                    <p><span class="font-medium">Database:</span> {{ $company->database }}</p>
                    <p><span class="font-medium">Server:</span> {{ $company->host }}:{{ $company->port }}</p>
                </div>
            </div>
            <button
                onclick="companySwitcher.openModal(false)"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                เปลี่ยนบริษัท
            </button>
        </div>
    </div>

    <!-- Placeholder for future widgets -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <!-- Future: Add quick access cards, statistics, etc. -->
    </div>
    @else
    <!-- No Company Selected State -->
    <div class="rounded-lg border border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">ยังไม่ได้เลือกบริษัท</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">กรุณาเลือกบริษัทเพื่อเริ่มใช้งานระบบ Bplus</p>
        <button
            onclick="companySwitcher.openModal(true)"
            class="mt-6 rounded-lg bg-brand-600 px-6 py-3 text-sm font-medium text-white hover:bg-brand-700"
        >
            เลือกบริษัท
        </button>
    </div>
    @endif
</div>
@endsection
