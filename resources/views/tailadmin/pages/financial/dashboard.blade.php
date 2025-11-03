@extends('tailadmin.layouts.app')

@section('title', 'Financial Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
<style>
    /* Dark mode สำหรับ DataTables */
    .dark .dataTables_wrapper {
        color: #e5e7eb;
    }
    .dark table.dataTable thead th,
    .dark table.dataTable thead td {
        color: #e5e7eb;
        border-bottom-color: #374151;
    }
    .dark table.dataTable tbody td {
        color: #d1d5db;
        border-bottom-color: #374151;
    }
    .dark table.dataTable.stripe tbody tr.odd,
    .dark table.dataTable.display tbody tr.odd {
        background-color: #1f2937;
    }
    .dark table.dataTable.hover tbody tr:hover,
    .dark table.dataTable.display tbody tr:hover {
        background-color: #374151;
    }
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #9ca3af !important;
    }
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #fff !important;
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .dark .dataTables_wrapper .dataTables_length select,
    .dark .dataTables_wrapper .dataTables_filter input {
        background-color: #374151;
        color: #e5e7eb;
        border-color: #4b5563;
    }

    /* Card borders ชัดเจนขึ้น */
    .card-white {
        border: 2px solid #E5E7EB;
        background-color: #FFFFFF;
    }

    .dark .card-white {
        border: 2px solid #475569;
        background-color: #0F172A;
    }

    .card-gradient {
        border: 2px solid #E5E7EB;
    }

    .dark .card-gradient {
        border: 2px solid #475569;
    }

    /* Account Balance Cards - สีเข้มขึ้นใน Dark mode */
    .card-account-blue {
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        border: 2px solid #BFDBFE;
    }

    .dark .card-account-blue {
        background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
        border: 2px solid #475569;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }

    .card-account-green {
        background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
        border: 2px solid #BBF7D0;
    }

    .dark .card-account-green {
        background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
        border: 2px solid #475569;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }

    .card-account-slate {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 2px solid #E2E8F0;
    }

    .dark .card-account-slate {
        background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
        border: 2px solid #475569;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    }

    /* Cheque Summary Cards - Dark Mode */
    .dark a.card-gradient {
        background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%) !important;
        border: 2px solid #475569 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2) !important;
    }

    .dark a.card-gradient:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3) !important;
    }

    /* Table dark mode - เพิ่มความชัด */
    .dark table tbody tr {
        background-color: #1E293B;
    }

    .dark table tbody tr:nth-child(even) {
        background-color: #0F172A;
    }

    .dark table tbody tr:hover {
        background-color: #334155 !important;
    }

    .dark table thead tr {
        background-color: #334155 !important;
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Financial Dashboard
        </h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-primary" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium">Financial Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Company Info -->
    <div class="mb-6 rounded-lg px-7.5 py-4 shadow-default card-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-black dark:text-white">
                    {{ $company->label }}
                </h3>
                <p class="text-sm text-bodydark">
                    Company Code: {{ $company->key }}
                </p>
            </div>
            <button
                onclick="financialDashboard.refreshAll()"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-center font-medium text-white hover:bg-opacity-90 lg:px-5 xl:px-6"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Account Summary Cards -->
    <div class="mb-6">
        <h3 class="mb-4 text-xl font-semibold text-black dark:text-white">Account Balances</h3>

        <div id="account-loading" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Loading Skeleton -->
            <div class="animate-pulse rounded-lg p-6 shadow-default card-white">
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 mb-4 w-1/2"></div>
                <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
            </div>
            <div class="animate-pulse rounded-lg p-6 shadow-default card-white">
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 mb-4 w-1/2"></div>
                <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
            </div>
            <div class="animate-pulse rounded-lg p-6 shadow-default card-white">
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 mb-4 w-1/2"></div>
                <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
            </div>
            <div class="animate-pulse rounded-lg p-6 shadow-default card-white">
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 mb-4 w-1/2"></div>
                <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-3/4"></div>
            </div>
        </div>

        <div id="account-summary" class="hidden grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Current Accounts Card (กระแสรายวัน) -->
            <div class="rounded-lg p-6 shadow-lg card-account-blue">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-black dark:text-white">กระแสรายวัน</h4>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="h-5 w-5 fill-blue-600 dark:fill-blue-400" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                        </svg>
                    </span>
                </div>
                <div>
                    <h5 id="current-total" class="text-2xl font-bold text-black dark:text-white mb-2">0.00</h5>
                    <p id="current-accounts" class="text-sm text-bodydark">0 บัญชี</p>
                </div>
            </div>

            <!-- Savings Accounts Card (ออมทรัพย์) -->
            <div class="rounded-lg p-6 shadow-lg card-account-green">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-black dark:text-white">ออมทรัพย์</h4>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-5 w-5 fill-green-600 dark:fill-green-400" viewBox="0 0 24 24">
                            <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                        </svg>
                    </span>
                </div>
                <div>
                    <h5 id="savings-total" class="text-2xl font-bold text-black dark:text-white mb-2">0.00</h5>
                    <p id="savings-accounts" class="text-sm text-bodydark">0 บัญชี</p>
                </div>
            </div>

            <!-- Other Accounts Card (ไม่ระบุ) -->
            <div class="rounded-lg p-6 shadow-lg card-account-slate">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-black dark:text-white">ไม่ระบุ</h4>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700">
                        <svg class="h-5 w-5 fill-slate-600 dark:fill-slate-400" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                        </svg>
                    </span>
                </div>
                <div>
                    <h5 id="cash-total" class="text-2xl font-bold text-black dark:text-white mb-2">0.00</h5>
                    <p id="cash-accounts" class="text-sm text-bodydark">0 บัญชี</p>
                </div>
            </div>

            <!-- Grand Total Card -->
            <div class="rounded-lg bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900 dark:to-pink-900 p-6 shadow-lg card-gradient">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-black dark:text-white">รวมทั้งหมด</h4>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-800">
                        <svg class="h-5 w-5 fill-purple-600 dark:fill-purple-300" viewBox="0 0 24 24">
                            <path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                        </svg>
                    </span>
                </div>
                <div>
                    <h5 id="grand-total" class="text-2xl font-bold text-black dark:text-white mb-2">0.00</h5>
                    <p class="text-sm text-bodydark">ทุกบัญชี</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cheque Summary -->
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- เช็คเกินกำหนด -->
        <a href="{{ route('financial.cheques.index', ['period' => 'overdue']) }}" class="block rounded-lg bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900 dark:to-rose-900 p-6 shadow-lg card-gradient hover:shadow-xl transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-medium text-black dark:text-white">เช็คเกินกำหนด</h4>
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-5 w-5 fill-red-600 dark:fill-red-400" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </span>
            </div>
            <div id="overdue-cheques-loading" class="animate-pulse">
                <div class="h-8 bg-red-200 rounded dark:bg-red-900 mb-2"></div>
                <div class="h-4 bg-red-200 rounded dark:bg-red-900 w-3/4"></div>
            </div>
            <div id="overdue-cheques" class="hidden">
                <h5 class="text-2xl font-bold text-black dark:text-white mb-2">
                    <span id="overdue-count">0</span> เช็ค
                </h5>
                <p class="text-sm text-black dark:text-white">
                    <span id="overdue-amount" class="font-semibold">0.00</span> บาท
                </p>
            </div>
        </a>

        <!-- เช็คใกล้ครบกำหนด 7 วัน -->
        <a href="{{ route('financial.cheques.index', ['period' => '7days']) }}" class="block rounded-lg bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-800 dark:to-orange-800 p-6 shadow-lg card-gradient hover:shadow-xl transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-medium text-black dark:text-white">ภายใน 7 วัน</h4>
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900">
                    <svg class="h-5 w-5 fill-amber-600 dark:fill-amber-400" viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                </span>
            </div>
            <div id="within7-cheques-loading" class="animate-pulse">
                <div class="h-8 bg-amber-200 rounded dark:bg-amber-900 mb-2"></div>
                <div class="h-4 bg-amber-200 rounded dark:bg-amber-900 w-3/4"></div>
            </div>
            <div id="within7-cheques" class="hidden">
                <h5 class="text-2xl font-bold text-black dark:text-white mb-2">
                    <span id="within7-count">0</span> เช็ค
                </h5>
                <p class="text-sm text-black dark:text-white">
                    <span id="within7-amount" class="font-semibold">0.00</span> บาท
                </p>
            </div>
        </a>

        <!-- เช็ค 8-14 วัน -->
        <a href="{{ route('financial.cheques.index', ['period' => '14days']) }}" class="block rounded-lg bg-gradient-to-br from-yellow-50 to-lime-50 dark:from-yellow-800 dark:to-lime-800 p-6 shadow-lg card-gradient hover:shadow-xl transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-medium text-black dark:text-white">8-14 วัน</h4>
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-5 w-5 fill-yellow-600 dark:fill-yellow-400" viewBox="0 0 24 24">
                        <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                    </svg>
                </span>
            </div>
            <div id="within14-cheques-loading" class="animate-pulse">
                <div class="h-8 bg-yellow-200 rounded dark:bg-yellow-900 mb-2"></div>
                <div class="h-4 bg-yellow-200 rounded dark:bg-yellow-900 w-3/4"></div>
            </div>
            <div id="within14-cheques" class="hidden">
                <h5 class="text-2xl font-bold text-black dark:text-white mb-2">
                    <span id="within14-count">0</span> เช็ค
                </h5>
                <p class="text-sm text-black dark:text-white">
                    <span id="within14-amount" class="font-semibold">0.00</span> บาท
                </p>
            </div>
        </a>

        <!-- เช็ค 15-30 วัน -->
        <a href="{{ route('financial.cheques.index', ['period' => '30days']) }}" class="block rounded-lg bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-800 dark:to-teal-800 p-6 shadow-lg card-gradient hover:shadow-xl transition-all cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-medium text-black dark:text-white">15-30 วัน</h4>
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
                    <svg class="h-5 w-5 fill-emerald-600 dark:fill-emerald-400" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </span>
            </div>
            <div id="within30-cheques-loading" class="animate-pulse">
                <div class="h-8 bg-emerald-200 rounded dark:bg-emerald-900 mb-2"></div>
                <div class="h-4 bg-emerald-200 rounded dark:bg-emerald-900 w-3/4"></div>
            </div>
            <div id="within30-cheques" class="hidden">
                <h5 class="text-2xl font-bold text-black dark:text-white mb-2">
                    <span id="within30-count">0</span> เช็ค
                </h5>
                <p class="text-sm text-black dark:text-white">
                    <span id="within30-amount" class="font-semibold">0.00</span> บาท
                </p>
            </div>
        </a>
    </div>

    <!-- Buttons to show tables -->
    <div class="mb-6 flex gap-4">
        <button onclick="financialDashboard.showAllCheques()"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 dark:bg-blue-700 px-6 py-3 text-center font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600">
            แสดงตารางเช็คทั้งหมด
        </button>
    </div>

    <!-- Detailed Account List -->
    <div class="mb-6 rounded-lg shadow-default card-white">
        <div class="border-b border-stroke px-7.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">
                รายละเอียดบัญชีทั้งหมด
            </h3>
        </div>

        <div id="account-details-loading" class="p-7.5">
            <div class="animate-pulse space-y-4">
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
                <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-full"></div>
            </div>
        </div>

        <div id="account-details" class="hidden p-7.5">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="px-4 py-4 font-medium text-black dark:text-white">ประเภท</th>
                            <th class="px-4 py-4 font-medium text-black dark:text-white">รหัสบัญชี</th>
                            <th class="px-4 py-4 font-medium text-black dark:text-white">ชื่อบัญชี</th>
                            <th class="px-4 py-4 font-medium text-black dark:text-white">ธุรกรรมล่าสุด</th>
                            <th class="px-4 py-4 font-medium text-black dark:text-white text-right">ยอดคงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody id="account-table-body" class="text-black dark:text-white">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cheques Table -->
    <div id="cheques-due-section" class="hidden mb-6 rounded-lg shadow-default card-white">
        <div class="border-b border-stroke px-7.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">
                รายละเอียดเช็คทั้งหมด
            </h3>
        </div>

        <div class="p-7.5">
            <div class="overflow-x-auto">
                <table id="cheques-table" class="w-full display stripe hover">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-medium text-black dark:text-white">สถานะ</th>
                            <th class="px-4 py-3 text-left font-medium text-black dark:text-white">เลขที่เช็ค</th>
                            <th class="px-4 py-3 text-left font-medium text-black dark:text-white">วันครบกำหนด</th>
                            <th class="px-4 py-3 text-left font-medium text-black dark:text-white">วันที่อ้างอิง</th>
                            <th class="px-4 py-3 text-left font-medium text-black dark:text-white">ผู้รับเงิน</th>
                            <th class="px-4 py-3 text-right font-medium text-black dark:text-white">ยอดคงค้าง</th>
                        </tr>
                    </thead>
                    <tbody id="cheques-table-body" class="text-black dark:text-white">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>

            <div id="no-cheques-due" class="hidden py-8 text-center text-gray-600 dark:text-gray-400">
                ไม่มีข้อมูลเช็ค
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
@endpush

<script>
const financialDashboard = {
    async loadAccountSummary() {
        try {
            const response = await fetch('{{ route("financial.dashboard.accounts") }}');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            // Hide loading, show summary
            document.getElementById('account-loading').classList.add('hidden');
            document.getElementById('account-summary').classList.remove('hidden');

            // Update summary cards
            const { summary, grand_total } = data;

            // Current (กระแสรายวัน)
            document.getElementById('current-total').textContent = this.formatNumber(summary.current.total);
            document.getElementById('current-accounts').textContent = `${summary.current.accounts.length} บัญชี`;

            // Savings (ออมทรัพย์)
            document.getElementById('savings-total').textContent = this.formatNumber(summary.savings.total);
            document.getElementById('savings-accounts').textContent = `${summary.savings.accounts.length} บัญชี`;

            // Other (ไม่ระบุ) - ใช้ card ที่ 3 แทน cash
            document.getElementById('cash-total').textContent = this.formatNumber(summary.other.total);
            document.getElementById('cash-accounts').textContent = `${summary.other.accounts.length} บัญชี`;

            // Grand Total
            document.getElementById('grand-total').textContent = this.formatNumber(grand_total);

            // Load detailed table
            this.renderAccountTable(data.all_accounts);

        } catch (error) {
            console.error('Failed to load account summary:', error);
            alert('ไม่สามารถโหลดข้อมูลบัญชีได้: ' + error.message);
        }
    },

    renderAccountTable(accounts) {
        const tbody = document.getElementById('account-table-body');
        tbody.innerHTML = '';

        accounts.forEach(account => {
            const row = `
                <tr class="border-b border-stroke dark:border-strokedark hover:bg-gray-2 dark:hover:bg-meta-4 cursor-pointer"
                    onclick="financialDashboard.showAccountStatement('${account.BNKAC_CODE}')">
                    <td class="px-4 py-4">
                        <span class="inline-flex rounded-full bg-opacity-10 px-3 py-1 text-xs font-medium ${this.getTypeColor(account.BNKAC_AC_TYPE)}">
                            ${account.AccountTypeName}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-black dark:text-white">${account.BNKAC_CODE}</td>
                    <td class="px-4 py-4">${account.BNKAC_NAME}</td>
                    <td class="px-4 py-4">${account.LastTxnDate ? this.formatDate(account.LastTxnDate) : '-'}</td>
                    <td class="px-4 py-4 text-right font-medium">${this.formatNumber(account.EndingBalance)}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        document.getElementById('account-details-loading').classList.add('hidden');
        document.getElementById('account-details').classList.remove('hidden');
    },

    getTypeColor(acType) {
        const colors = {
            '1': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',      // กระแสรายวัน
            '2': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',  // ออมทรัพย์
        };
        return colors[acType] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';  // ไม่ระบุ
    },

    showAccountStatement(accountCode) {
        // TODO: Implement account statement modal
        alert(`Statement for account: ${accountCode}\n(ยังไม่ได้ทำ - รอ query statement)`);
    },

    async loadChequeSummary() {
        try {
            const response = await fetch('{{ route("financial.dashboard.cheques") }}');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            // แสดงสรุปตามช่วงเวลา
            const summary = data.summary || [];

            // จัดกลุ่มข้อมูลตาม PeriodBucket
            const buckets = {
                'เกินกำหนด': { count: 0, amount: 0 },
                'ภายใน 7 วัน': { count: 0, amount: 0 },
                '8–14 วัน': { count: 0, amount: 0 },
                '15–30 วัน': { count: 0, amount: 0 }
            };

            summary.forEach(item => {
                if (buckets[item.PeriodBucket]) {
                    buckets[item.PeriodBucket].count = item.ChequeCount || 0;
                    buckets[item.PeriodBucket].amount = item.TotalOutstandingAmt || 0;
                }
            });

            // Update เช็คเกินกำหนด
            document.getElementById('overdue-cheques-loading').classList.add('hidden');
            document.getElementById('overdue-cheques').classList.remove('hidden');
            document.getElementById('overdue-count').textContent = buckets['เกินกำหนด'].count;
            document.getElementById('overdue-amount').textContent = this.formatNumber(buckets['เกินกำหนด'].amount);

            // Update ภายใน 7 วัน
            document.getElementById('within7-cheques-loading').classList.add('hidden');
            document.getElementById('within7-cheques').classList.remove('hidden');
            document.getElementById('within7-count').textContent = buckets['ภายใน 7 วัน'].count;
            document.getElementById('within7-amount').textContent = this.formatNumber(buckets['ภายใน 7 วัน'].amount);

            // Update 8-14 วัน
            document.getElementById('within14-cheques-loading').classList.add('hidden');
            document.getElementById('within14-cheques').classList.remove('hidden');
            document.getElementById('within14-count').textContent = buckets['8–14 วัน'].count;
            document.getElementById('within14-amount').textContent = this.formatNumber(buckets['8–14 วัน'].amount);

            // Update 15-30 วัน
            document.getElementById('within30-cheques-loading').classList.add('hidden');
            document.getElementById('within30-cheques').classList.remove('hidden');
            document.getElementById('within30-count').textContent = buckets['15–30 วัน'].count;
            document.getElementById('within30-amount').textContent = this.formatNumber(buckets['15–30 วัน'].amount);

        } catch (error) {
            console.error('Failed to load cheque summary:', error);
        }
    },

    async showAllCheques() {
        try {
            const response = await fetch(`{{ route("financial.dashboard.cheques.due") }}`);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            document.getElementById('cheques-due-section').classList.remove('hidden');

            if (data.cheques.length === 0) {
                document.getElementById('no-cheques-due').classList.remove('hidden');
                return;
            }

            document.getElementById('no-cheques-due').classList.add('hidden');

            // ถ้ามี DataTable อยู่แล้ว ให้ทำลายก่อน
            if ($.fn.DataTable.isDataTable('#cheques-table')) {
                $('#cheques-table').DataTable().destroy();
            }

            const tbody = document.getElementById('cheques-table-body');
            tbody.innerHTML = '';

            data.cheques.forEach(cheque => {
                const row = `
                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-800">
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium ${this.getPeriodColor(cheque.PeriodBucket)}">
                                ${cheque.PeriodBucket}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">${cheque.CQBK_CHEQUE_NO || '-'}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${this.formatDate(cheque.CQBK_CHEQUE_DD)}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${this.formatDate(cheque.CQBK_REFER_DATE)}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${cheque.CQBK_PAY || '-'}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">${this.formatNumber(cheque.OutstandingAmt)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // เริ่มต้น DataTable
            $('#cheques-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                },
                order: [[2, 'asc']], // เรียงตามวันครบกำหนด
                pageLength: 25,
                responsive: true,
                dom: 'Bfrtip',
                buttons: []
            });

        } catch (error) {
            console.error('Failed to load cheques:', error);
            alert('ไม่สามารถโหลดข้อมูลเช็คได้: ' + error.message);
        }
    },

    async showChequeDetail(chequeNo) {
        // TODO: Implement modal to show cheque details
        alert(`Cheque Detail for: ${chequeNo}`);
    },

    formatNumber(num) {
        return parseFloat(num).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    getPeriodColor(period) {
        const colors = {
            'เกินกำหนด': 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            'ภายใน 7 วัน': 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
            '8–14 วัน': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            '15–30 วัน': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
            'มากกว่า 30 วัน': 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300'
        };
        return colors[period] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    },

    async refreshAll() {
        location.reload();
    },

    init() {
        this.loadAccountSummary();
        this.loadChequeSummary();
    }
};

// Load data when page is ready
document.addEventListener('DOMContentLoaded', () => {
    financialDashboard.init();
});
</script>
@endsection
