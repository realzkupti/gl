@extends('tailadmin.layouts.app')

@section('title', 'จัดการสิทธิ์ - ' . $user->name)

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            จัดการสิทธิ์: {{ $user->name }}
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-500 hover:text-brand-500">Dashboard</a></li>
                <li><span class="text-gray-400">/</span></li>
                <li><a href="{{ route('admin.user-permissions') }}" class="font-medium text-gray-500 hover:text-brand-500">จัดการสิทธิ์</a></li>
                <li><span class="text-gray-400">/</span></li>
                <li class="font-medium text-brand-500">{{ $user->name }}</li>
            </ol>
        </nav>
    </div>

    <!-- User Info Card -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-500 text-white">
                <span class="text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('status'))
    <div class="mb-6 rounded-lg border-l-4 border-green-500 bg-green-50 p-4 dark:bg-green-900/20">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-green-700 dark:text-green-400 font-medium">{{ session('status') }}</p>
        </div>
    </div>
    @endif

    <!-- Permission Form -->
    <form id="permissionForm" method="POST" action="{{ route('admin.user-permissions.update', $user->id) }}">
        @csrf
        @method('PUT')

        <!-- Tabs -->
        <div class="mb-6" x-data="{ activeTab: 'default' }">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <button type="button" @click="activeTab = 'default'"
                        :class="activeTab === 'default' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                        <svg class="inline w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        สิทธิ์เมนูทั่วไป
                    </button>
                    <button type="button" @click="activeTab = 'bplus'"
                        :class="activeTab === 'bplus' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                        <svg class="inline w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        สิทธิ์ BPLUS (แบ่งตามบริษัท)
                    </button>
                </nav>
            </div>

            <!-- Default Permissions Tab -->
            <div x-show="activeTab === 'default'" class="mt-6">
                <!-- Action Buttons (Top) -->
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        เลือกสิทธิ์สำหรับแต่ละเมนู (ถ้าไม่เลือก = ไม่มีสิทธิ์)
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="selectAll" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 shadow-md hover:shadow-lg transition-all border-2 border-brand-700 dark:border-brand-500 dark:bg-brand-600 dark:hover:bg-brand-700">
                            <svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            เลือกทั้งหมด
                        </button>
                        <button type="button" id="deselectAll" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 shadow-md hover:shadow-lg transition-all border-2 border-gray-300 dark:text-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-500">
                            <svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            ยกเลิกทั้งหมด
                        </button>
                    </div>
                </div>

                @php
                    // Filter menus by department - show menus NOT from Bplus department
                    $defaultMenus = $menus->filter(function($m){
                        return !$m->department || ($m->department->key ?? '') !== 'bplus';
                    })->values();
                @endphp
                <!-- Default Permissions Table -->
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-800 dark:text-gray-400 z-10">
                                        เมนู
                                    </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>ดู</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>สร้าง</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>แก้ไข</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>ลบ</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Export</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>อนุมัติ</span>
                                </div>
                            </th>
                        </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                                @if($defaultMenus->isEmpty())
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-gray-600 dark:text-gray-400 mb-3">ยังไม่มีเมนูสำหรับกำหนดสิทธิ์</p>
                                        <a href="{{ route('admin.menus') }}" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">ไปหน้าจัดการเมนู</a>
                                    </td>
                                </tr>
                                @endif
                                @foreach ($defaultMenus as $menu)
                                @php
                                    $perm = $userPermissions->get($menu->id);
                                    $deptPerm = $departmentPermissions->get($menu->id);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-900">
                                        <div>
                                            <div class="font-semibold">{{ $menu->label }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $menu->key }}</div>
                                            @if($deptPerm)
                                            <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                                แผนก: {{ $deptPerm->can_view ? '👁️' : '' }}{{ $deptPerm->can_create ? '➕' : '' }}{{ $deptPerm->can_update ? '✏️' : '' }}{{ $deptPerm->can_delete ? '🗑️' : '' }}{{ $deptPerm->can_export ? '📥' : '' }}{{ $deptPerm->can_approve ? '✅' : '' }}
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                            <td class="px-4 py-4 text-center">
                                <input type="hidden" name="permissions[{{ $loop->index }}][menu_id]" value="{{ $menu->id }}">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_view]" value="1"
                                    {{ $perm && $perm->can_view ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_create]" value="1"
                                    {{ $perm && $perm->can_create ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_update]" value="1"
                                    {{ $perm && $perm->can_update ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_delete]" value="1"
                                    {{ $perm && $perm->can_delete ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_export]" value="1"
                                    {{ $perm && $perm->can_export ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_approve]" value="1"
                                    {{ $perm && $perm->can_approve ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BPLUS Permissions Tab -->
            <div x-show="activeTab === 'bplus'" class="mt-6" style="display: none;">
                <div class="mb-6 rounded-lg border-l-4 border-orange-500 bg-orange-50 p-4 dark:bg-orange-900/20">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-orange-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-orange-700 dark:text-orange-400 font-medium mb-1">สิทธิ์ BPLUS แบบแยกตามบริษัท</p>
                            <p class="text-sm text-orange-600 dark:text-orange-400">เลือกบริษัทที่ user สามารถเข้าถึงได้สำหรับแต่ละเมนู BPLUS (ต้องเปิดสิทธิ์ "ดู" ในแท็บ "สิทธิ์เมนูทั่วไป" ก่อนจึงจะมีผล)</p>
                        </div>
                    </div>
                </div>

                @php
                    // Filter menus by Bplus department
                    $bplusMenus = $menus->filter(function($m){
                        return $m->department && ($m->department->key ?? '') === 'bplus';
                    })->values();
                @endphp

                @if($bplusMenus->isEmpty())
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p>ยังไม่มีเมนู BPLUS ในระบบ</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($bplusMenus as $menu)
                            @php
                                $selectedCompanies = $userCompanyAccess->get($menu->id, []);
                            @endphp
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $menu->label }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $menu->key }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400">
                                        BPLUS
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($companies as $company)
                                        <label class="flex items-center p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
                                            <input type="checkbox"
                                                name="menu_company_access[{{ $menu->id }}][]"
                                                value="{{ $company->id }}"
                                                {{ in_array($company->id, $selectedCompanies) ? 'checked' : '' }}
                                                class="w-5 h-5 text-orange-600 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 cursor-pointer">
                                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">{{ $company->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons (Bottom) -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 sticky bottom-0 bg-white dark:bg-gray-950 py-4 border-t-2 border-gray-200 dark:border-gray-700 -mx-4 px-4 md:-mx-6 md:px-6 2xl:-mx-10 2xl:px-10 z-20 shadow-lg">
            <button type="button" onclick="if(confirm('ยืนยันการล้างสิทธิ์ทั้งหมด?')) { document.getElementById('resetForm').submit(); }"
                class="w-full sm:w-auto rounded-lg bg-red-100 hover:bg-red-200 px-6 py-3 text-red-700 font-semibold transition shadow-md hover:shadow-lg border-2 border-red-300 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 dark:border-red-700">
                <svg class="w-5 h-5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                ล้างสิทธิ์ทั้งหมด
            </button>
            <div class="flex gap-3 w-full sm:w-auto">
                <a href="{{ route('admin.user-permissions') }}" class="flex-1 sm:flex-none text-center rounded-lg bg-gray-200 hover:bg-gray-300 px-6 py-3 text-gray-700 font-semibold transition shadow-md hover:shadow-lg border-2 border-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:border-gray-500">
                    <svg class="w-5 h-5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    ยกเลิก
                </a>
                <button type="submit" id="savePermissionsBtn" class="flex-1 sm:flex-none rounded-lg bg-brand-600 hover:bg-brand-700 px-8 py-3 text-white font-bold shadow-lg hover:shadow-xl transition-all border-2 border-brand-700 dark:border-brand-500 dark:bg-brand-600 dark:hover:bg-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    บันทึกสิทธิ์
                </button>
            </div>
        </div>
    </form>

    <!-- Separate form for reset (hidden) -->
    <form id="resetForm" method="POST" action="{{ route('admin.user-permissions.reset', $user->id) }}" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
// Select/Deselect All functionality
document.getElementById('selectAll').addEventListener('click', function() {
    // Only select checkboxes in the default permissions table
    document.querySelectorAll('table input[type="checkbox"]').forEach(cb => cb.checked = true);
});

document.getElementById('deselectAll').addEventListener('click', function() {
    // Only deselect checkboxes in the default permissions table
    document.querySelectorAll('table input[type="checkbox"]').forEach(cb => cb.checked = false);
});

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 animate-slide-in`;
    toast.innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${type === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : type === 'error'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            }
        </svg>
        <span class="font-medium">${message}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Handle form submission with Fetch API
document.getElementById('permissionForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const submitBtn = document.getElementById('savePermissionsBtn');
    const originalBtnHtml = submitBtn.innerHTML;

    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        กำลังบันทึก...
    `;

    // Prepare form data
    const formData = new FormData(form);

    // Send request
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'บันทึกสิทธิ์สำเร็จ', 'success');

            // Reload page after short delay to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('เกิดข้อผิดพลาดในการบันทึก', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHtml;
    });
});

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endpush
@endsection
