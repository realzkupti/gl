@extends('tailadmin.layouts.app')

@section('title', 'จัดการสิทธิ์แผนก - ' . $department->label)

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            จัดการสิทธิ์แผนก: {{ $department->label }}
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-500 hover:text-brand-500">Dashboard</a></li>
                <li><span class="text-gray-400">/</span></li>
                <li><a href="{{ route('admin.department-permissions.index') }}" class="font-medium text-gray-500 hover:text-brand-500">จัดการสิทธิ์แผนก</a></li>
                <li><span class="text-gray-400">/</span></li>
                <li class="font-medium text-brand-500">{{ $department->label }}</li>
            </ol>
        </nav>
    </div>

    <!-- Department Info Card -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-500 text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $department->label }}</h3>
                <p class="text-gray-600 dark:text-gray-400 font-mono text-sm">{{ $department->key }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-600 dark:text-gray-400">ผู้ใช้ในแผนก</div>
                <div class="text-2xl font-bold text-brand-600">{{ $department->users()->count() }}</div>
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
    <form method="POST" action="{{ route('admin.department-permissions.update', $department->id) }}">
        @csrf
        @method('PUT')

        <!-- Action Buttons (Top) -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                เลือกสิทธิ์สำหรับแต่ละเมนู (ถ้าไม่เลือก = ไม่มีสิทธิ์)
            </div>
            <div class="flex gap-2">
                <button type="button" id="selectAll" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 shadow-md hover:shadow-lg transition-all border-2 border-green-700 dark:border-green-500 dark:bg-green-600 dark:hover:bg-green-700">
                    <svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    เลือกทั้งหมด
                </button>
                <button type="button" id="deselectAll" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-gray-900 bg-gray-200 hover:bg-gray-300 shadow-md hover:shadow-lg transition-all border-2 border-gray-400 dark:text-white dark:bg-gray-600 dark:hover:bg-gray-500 dark:border-gray-400">
                    <svg class="w-4 h-4 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    ยกเลิกทั้งหมด
                </button>
            </div>
        </div>

        <!-- Permissions Table -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-800 dark:text-gray-400 z-10">
                                เมนู
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-purple-50 dark:bg-purple-900/20">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-purple-700 dark:text-purple-300 font-bold">ทั้งหมด</span>
                                </div>
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
                        @php
                            // Group all menus by system_type (1 = ระบบ, 2 = Bplus)
                            $systemMenus = $menus->where('system_type', 1)->sortBy('sort_order')->values();
                            $bplusMenus = $menus->where('system_type', 2)->sortBy('sort_order')->values();

                            // Separate parent and child menus for hierarchical display
                            $systemParents = $systemMenus->where('parent_id', null);
                            $systemChildren = $systemMenus->where('parent_id', '!=', null)->groupBy('parent_id');

                            $bplusParents = $bplusMenus->where('parent_id', null);
                            $bplusChildren = $bplusMenus->where('parent_id', '!=', null)->groupBy('parent_id');

                            $loopIndex = 0; // Manual loop counter for permissions array
                        @endphp

                        @if($menus->isEmpty())
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-gray-600 dark:text-gray-400 mb-3">ยังไม่มีเมนูสำหรับกำหนดสิทธิ์</p>
                                <a href="{{ route('admin.menus') }}" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">ไปหน้าจัดการเมนู</a>
                            </td>
                        </tr>
                        @endif

                        {{-- ระบบ (System) Section --}}
                        @if($systemParents->isNotEmpty())
                        <tr class="bg-blue-50 dark:bg-blue-900/20">
                            <td colspan="8" class="px-6 py-3 text-sm font-bold text-blue-900 dark:text-blue-300">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                </svg>
                                ระบบ (System)
                            </td>
                        </tr>
                        @foreach ($systemParents as $menu)
                        @php
                            $perm = $permissions->get($menu->id);
                        @endphp
                        {{-- Parent Menu Row --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition parent-menu-row" data-menu-id="{{ $menu->id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-900">
                                <div>
                                    <div class="font-semibold">{{ $menu->label }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $menu->key }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center bg-purple-50 dark:bg-purple-900/10">
                                <input type="checkbox" class="select-all-permissions w-5 h-5 text-purple-600 border-purple-300 dark:border-purple-600 rounded focus:ring-purple-500 cursor-pointer"
                                       data-row-index="{{ $loopIndex }}">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="hidden" name="permissions[{{ $loopIndex }}][menu_id]" value="{{ $menu->id }}">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_view]" value="1"
                                    {{ $perm && $perm->can_view ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_view"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_create]" value="1"
                                    {{ $perm && $perm->can_create ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_create"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_update]" value="1"
                                    {{ $perm && $perm->can_update ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_update"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_delete]" value="1"
                                    {{ $perm && $perm->can_delete ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_delete"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_export]" value="1"
                                    {{ $perm && $perm->can_export ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_export"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_approve]" value="1"
                                    {{ $perm && $perm->can_approve ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_approve"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                        </tr>
                        @php $loopIndex++; @endphp

                        {{-- Child Menus for System --}}
                        @if(isset($systemChildren[$menu->id]))
                            @foreach($systemChildren[$menu->id] as $childMenu)
                            @php
                                $childPerm = $permissions->get($childMenu->id);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition child-menu-row bg-gray-50/50 dark:bg-gray-800/50" data-parent-id="{{ $menu->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-gray-50 dark:bg-gray-800">
                                    <div class="pl-6">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <div>
                                                <div class="font-medium">{{ $childMenu->label }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $childMenu->key }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center bg-purple-50 dark:bg-purple-900/10">
                                    <input type="checkbox" class="select-all-permissions w-5 h-5 text-purple-600 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-500 cursor-pointer" data-row-index="{{ $loopIndex }}">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="hidden" name="permissions[{{ $loopIndex }}][menu_id]" value="{{ $childMenu->id }}">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_view]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_view ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_create]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_create ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_update]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_update ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_delete]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_delete ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_export]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_export ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_approve]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_approve ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                            </tr>
                            @php $loopIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                        @endif

                        {{-- Bplus Section --}}
                        @if($bplusParents->isNotEmpty())
                        <tr class="bg-orange-50 dark:bg-orange-900/20">
                            <td colspan="8" class="px-6 py-3 text-sm font-bold text-orange-900 dark:text-orange-300">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Bplus
                            </td>
                        </tr>
                        @foreach ($bplusParents as $menu)
                        @php
                            $perm = $permissions->get($menu->id);
                        @endphp
                        {{-- Parent Menu Row --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition parent-menu-row" data-menu-id="{{ $menu->id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-900">
                                <div>
                                    <div class="font-semibold">{{ $menu->label }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $menu->key }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center bg-purple-50 dark:bg-purple-900/10">
                                <input type="checkbox" class="select-all-permissions w-5 h-5 text-purple-600 border-purple-300 dark:border-purple-600 rounded focus:ring-purple-500 cursor-pointer" data-row-index="{{ $loopIndex }}">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="hidden" name="permissions[{{ $loopIndex }}][menu_id]" value="{{ $menu->id }}">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_view]" value="1"
                                    {{ $perm && $perm->can_view ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_view"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_create]" value="1"
                                    {{ $perm && $perm->can_create ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_create"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_update]" value="1"
                                    {{ $perm && $perm->can_update ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_update"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_delete]" value="1"
                                    {{ $perm && $perm->can_delete ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_delete"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_export]" value="1"
                                    {{ $perm && $perm->can_export ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_export"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_approve]" value="1"
                                    {{ $perm && $perm->can_approve ? 'checked' : '' }}
                                    data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_approve"
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                        </tr>
                        @php $loopIndex++; @endphp

                        {{-- Child Menus for Bplus --}}
                        @if(isset($bplusChildren[$menu->id]))
                            @foreach($bplusChildren[$menu->id] as $childMenu)
                            @php
                                $childPerm = $permissions->get($childMenu->id);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition child-menu-row bg-gray-50/50 dark:bg-gray-800/50" data-parent-id="{{ $menu->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-gray-50 dark:bg-gray-800">
                                    <div class="pl-6">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <div>
                                                <div class="font-medium">{{ $childMenu->label }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $childMenu->key }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center bg-purple-50 dark:bg-purple-900/10">
                                    <input type="checkbox" class="select-all-permissions w-5 h-5 text-purple-600 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-500 cursor-pointer" data-row-index="{{ $loopIndex }}">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="hidden" name="permissions[{{ $loopIndex }}][menu_id]" value="{{ $childMenu->id }}">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_view]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_view ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_create]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_create ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_update]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_update ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_delete]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_delete ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_export]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_export ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                                <td class="px-4 py-4 text-center bg-gray-50/50 dark:bg-gray-800/50">
                                    <input type="checkbox" name="permissions[{{ $loopIndex }}][can_approve]" value="1" data-row-index="{{ $loopIndex }}" data-parent-id="{{ $menu->id }}"
                                        {{ $childPerm && $childPerm->can_approve ? 'checked' : '' }}
                                        class="permission-checkbox child-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                                </td>
                            </tr>
                            @php $loopIndex++; @endphp
                            @endforeach
                        @endif
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons (Bottom) -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <button type="button" onclick="if(confirm('ยืนยันการล้างสิทธิ์ทั้งหมด?')) { document.getElementById('resetForm').submit(); }"
                class="w-full sm:w-auto rounded-lg bg-red-100 hover:bg-red-200 px-6 py-3 text-red-700 font-medium transition dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40">
                ล้างสิทธิ์ทั้งหมด
            </button>
            <div class="flex gap-3 w-full sm:w-auto">
                <a href="{{ route('admin.department-permissions.index') }}" class="flex-1 sm:flex-none text-center rounded-lg bg-gray-200 hover:bg-gray-300 px-6 py-3 text-gray-700 font-medium transition dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    ยกเลิก
                </a>
                <button type="submit" class="flex-1 sm:flex-none rounded-lg bg-blue-600 hover:bg-blue-700 px-8 py-3 text-white font-bold shadow-lg hover:shadow-xl transition-all border-2 border-blue-700 dark:border-blue-500 dark:bg-blue-600 dark:hover:bg-blue-700 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    บันทึกสิทธิ์
                </button>
            </div>
        </div>
    </form>

    <!-- Separate form for reset (hidden) -->
    <form id="resetForm" method="POST" action="{{ route('admin.department-permissions.reset', $department->id) }}" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('click', function() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
    updateSelectAllCheckboxes();
});
document.getElementById('deselectAll').addEventListener('click', function() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    updateSelectAllCheckboxes();
});

// "ทั้งหมด" checkbox - select/deselect all permissions for that row
document.querySelectorAll('.select-all-permissions').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const rowIndex = this.dataset.rowIndex;
        const isChecked = this.checked;

        // Find the row to check if it's parent or child
        const row = this.closest('tr');
        const isParentRow = row.classList.contains('parent-menu-row');
        const menuId = row.dataset.menuId;

        if (isParentRow && menuId) {
            // Parent row's "ทั้งหมด" - special behavior
            if (isChecked) {
                // Check only "can_view" for parent
                const parentViewCb = document.querySelector(
                    `.parent-permission[data-menu-id="${menuId}"][data-permission-type="can_view"]`
                );
                if (parentViewCb) parentViewCb.checked = true;

                // Check ALL permissions for children
                document.querySelectorAll(`.child-permission[data-parent-id="${menuId}"]`).forEach(cb => {
                    cb.checked = true;
                });
            } else {
                // Uncheck everything for parent and children
                document.querySelectorAll(`.parent-permission[data-menu-id="${menuId}"]`).forEach(cb => {
                    cb.checked = false;
                });
                document.querySelectorAll(`.child-permission[data-parent-id="${menuId}"]`).forEach(cb => {
                    cb.checked = false;
                });
            }
        } else {
            // Child row's "ทั้งหมด" or regular row - normal behavior
            document.querySelectorAll(`.permission-checkbox[data-row-index="${rowIndex}"]`).forEach(cb => {
                cb.checked = isChecked;
            });

            // Trigger parent-child logic for each checkbox
            document.querySelectorAll(`.permission-checkbox[data-row-index="${rowIndex}"]`).forEach(cb => {
                handlePermissionChange(cb);
            });
        }

        updateSelectAllCheckboxes();
    });
});

// Individual permission checkboxes - update "ทั้งหมด" state and handle parent-child logic
document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        handlePermissionChange(this);
        updateSelectAllCheckboxes();
    });
});

// Handle parent-child menu permission relationships
function handlePermissionChange(checkbox) {
    const isParent = checkbox.classList.contains('parent-permission');
    const isChild = checkbox.classList.contains('child-permission');
    const permissionType = checkbox.dataset.permissionType;
    const isChecked = checkbox.checked;

    // Auto-enable "can_view" when any other permission is checked
    if (isChecked && permissionType && permissionType !== 'can_view') {
        const rowIndex = checkbox.dataset.rowIndex;
        const viewCheckbox = document.querySelector(
            `.permission-checkbox[data-row-index="${rowIndex}"][data-permission-type="can_view"]`
        );
        if (viewCheckbox && !viewCheckbox.checked) {
            viewCheckbox.checked = true;
        }
    }

    if (isParent) {
        // Parent checkbox changed
        const menuId = checkbox.dataset.menuId;

        // If parent is unchecked, uncheck all children's same permission
        if (!isChecked && permissionType) {
            if (permissionType === 'can_view') {
                // If parent view is unchecked, uncheck ALL child permissions
                document.querySelectorAll(`.child-permission[data-parent-id="${menuId}"]`).forEach(childCb => {
                    childCb.checked = false;
                });
            }
        }
    }

    if (isChild) {
        // Child checkbox changed
        const parentId = checkbox.dataset.parentId;

        // If any child permission is checked, auto-enable parent "can_view"
        const anyChildChecked = Array.from(
            document.querySelectorAll(`.child-permission[data-parent-id="${parentId}"]`)
        ).some(cb => cb.checked);

        if (anyChildChecked) {
            // Find and check parent's "can_view" permission
            const parentViewCheckbox = document.querySelector(
                `.parent-permission[data-menu-id="${parentId}"][data-permission-type="can_view"]`
            );
            if (parentViewCheckbox && !parentViewCheckbox.checked) {
                parentViewCheckbox.checked = true;
            }
        }
    }
}

// Update "ทั้งหมด" checkboxes based on individual permission states
function updateSelectAllCheckboxes() {
    document.querySelectorAll('.select-all-permissions').forEach(selectAllCb => {
        const rowIndex = selectAllCb.dataset.rowIndex;
        const rowCheckboxes = document.querySelectorAll(`.permission-checkbox[data-row-index="${rowIndex}"]`);

        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);

        selectAllCb.checked = allChecked;
        selectAllCb.indeterminate = someChecked && !allChecked; // Show partial state
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectAllCheckboxes();
});
</script>
@endpush
@endsection
