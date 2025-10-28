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

        <!-- All Permissions (Combined) -->
        <div class="mb-6">
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
                <!-- Default Permissions Table -->
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
                                    $perm = $userPermissions->get($menu->id);
                                    $deptPerm = $departmentPermissions->get($menu->id);
                                @endphp
                                {{-- Parent Menu Row --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition parent-menu-row" data-menu-id="{{ $menu->id }}">
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
                            <td class="px-4 py-4 text-center bg-purple-50 dark:bg-purple-900/10">
                                <input type="checkbox" class="select-all-permissions w-5 h-5 text-purple-600 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-500 cursor-pointer" data-row-index="{{ $loopIndex }}">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="hidden" name="permissions[{{ $loopIndex }}][menu_id]" value="{{ $menu->id }}">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_view]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_view"
                                    {{ $perm && $perm->can_view ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_create]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_create"
                                    {{ $perm && $perm->can_create ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_update]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_update"
                                    {{ $perm && $perm->can_update ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_delete]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_delete"
                                    {{ $perm && $perm->can_delete ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_export]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_export"
                                    {{ $perm && $perm->can_export ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_approve]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_approve"
                                    {{ $perm && $perm->can_approve ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                                </tr>
                                @php $loopIndex++; @endphp

                                {{-- Child Menus for System --}}
                                @if(isset($systemChildren[$menu->id]))
                                    @foreach($systemChildren[$menu->id] as $childMenu)
                                    @php
                                        $childPerm = $userPermissions->get($childMenu->id);
                                        $childDeptPerm = $departmentPermissions->get($childMenu->id);
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
                                                        @if($childDeptPerm)
                                                        <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                                            แผนก: {{ $childDeptPerm->can_view ? '👁️' : '' }}{{ $childDeptPerm->can_create ? '➕' : '' }}{{ $childDeptPerm->can_update ? '✏️' : '' }}{{ $childDeptPerm->can_delete ? '🗑️' : '' }}{{ $childDeptPerm->can_export ? '📥' : '' }}{{ $childDeptPerm->can_approve ? '✅' : '' }}
                                                        </div>
                                                        @endif
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
                                    $perm = $userPermissions->get($menu->id);
                                    $deptPerm = $departmentPermissions->get($menu->id);
                                @endphp
                                {{-- Parent Menu Row --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition parent-menu-row" data-menu-id="{{ $menu->id }}">
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
                            <td class="px-4 py-4 text-center bg-purple-50 dark:bg-purple-900/10">
                                <input type="checkbox" class="select-all-permissions w-5 h-5 text-purple-600 border-gray-300 dark:border-gray-600 rounded focus:ring-purple-500 cursor-pointer" data-row-index="{{ $loopIndex }}">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="hidden" name="permissions[{{ $loopIndex }}][menu_id]" value="{{ $menu->id }}">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_view]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_view"
                                    {{ $perm && $perm->can_view ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_create]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_create"
                                    {{ $perm && $perm->can_create ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_update]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_update"
                                    {{ $perm && $perm->can_update ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_delete]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_delete"
                                    {{ $perm && $perm->can_delete ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_export]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_export"
                                    {{ $perm && $perm->can_export ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loopIndex }}][can_approve]" value="1" data-row-index="{{ $loopIndex }}" data-menu-id="{{ $menu->id }}" data-permission-type="can_approve"
                                    {{ $perm && $perm->can_approve ? 'checked' : '' }}
                                    class="permission-checkbox parent-permission w-5 h-5 text-brand-600 border-gray-300 dark:border-gray-600 rounded focus:ring-brand-500 cursor-pointer">
                            </td>
                                </tr>
                                @php $loopIndex++; @endphp

                                {{-- Child Menus for Bplus --}}
                                @if(isset($bplusChildren[$menu->id]))
                                    @foreach($bplusChildren[$menu->id] as $childMenu)
                                    @php
                                        $childPerm = $userPermissions->get($childMenu->id);
                                        $childDeptPerm = $departmentPermissions->get($childMenu->id);
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
                                                        @if($childDeptPerm)
                                                        <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                                            แผนก: {{ $childDeptPerm->can_view ? '👁️' : '' }}{{ $childDeptPerm->can_create ? '➕' : '' }}{{ $childDeptPerm->can_update ? '✏️' : '' }}{{ $childDeptPerm->can_delete ? '🗑️' : '' }}{{ $childDeptPerm->can_export ? '📥' : '' }}{{ $childDeptPerm->can_approve ? '✅' : '' }}
                                                        </div>
                                                        @endif
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
            </div>

        <!-- Action Buttons (Bottom) -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 sticky bottom-0 bg-white dark:bg-gray-800 py-4 border-t-2 border-gray-200 dark:border-gray-600 -mx-4 px-4 md:-mx-6 md:px-6 2xl:-mx-10 2xl:px-10 z-20 shadow-lg">
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
                <button type="submit" id="savePermissionsBtn" class="flex-1 sm:flex-none rounded-lg bg-blue-600 hover:bg-blue-700 px-8 py-3 text-white font-bold shadow-lg hover:shadow-xl transition-all border-2 border-blue-700 dark:border-blue-500 dark:bg-blue-600 dark:hover:bg-blue-700 flex items-center justify-center">
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
    // Only select permission checkboxes (not the "select all" column)
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
    // Update "ทั้งหมด" checkboxes
    updateSelectAllCheckboxes();
});

document.getElementById('deselectAll').addEventListener('click', function() {
    // Only deselect permission checkboxes (not the "select all" column)
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    // Update "ทั้งหมด" checkboxes
    updateSelectAllCheckboxes();
});

// Handle "ทั้งหมด" checkbox - select/deselect all permissions for that row
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

// Handle individual permission checkboxes - update "ทั้งหมด" checkbox
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

// Function to update "ทั้งหมด" checkboxes based on row permissions
function updateSelectAllCheckboxes() {
    document.querySelectorAll('.select-all-permissions').forEach(selectAllCb => {
        const rowIndex = selectAllCb.dataset.rowIndex;
        const rowCheckboxes = document.querySelectorAll(`.permission-checkbox[data-row-index="${rowIndex}"]`);

        // Check if all permissions in this row are checked
        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);

        selectAllCb.checked = allChecked;
        selectAllCb.indeterminate = someChecked && !allChecked;
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectAllCheckboxes();
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

@if(isset($currentMenu) && $currentMenu && $currentMenu->has_sticky_note)
    <x-sticky-note
        :menu-id="$currentMenu->id"
        :company-id="session('current_company_id')"
    />
@endif

@endsection
