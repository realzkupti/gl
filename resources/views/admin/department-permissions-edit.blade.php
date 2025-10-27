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
                <button type="button" id="selectAll" class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 shadow-sm transition">
                    เลือกทั้งหมด
                </button>
                <button type="button" id="deselectAll" class="rounded-lg px-4 py-2 text-sm font-medium text-brand-700 bg-brand-50 ring-1 ring-inset ring-brand-300 hover:bg-brand-100 transition dark:text-brand-300 dark:bg-brand-900/20 dark:ring-brand-800 dark:hover:bg-brand-900/30">
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
                            <td colspan="7" class="px-6 py-10 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-gray-600 dark:text-gray-400 mb-3">ยังไม่มีเมนูสำหรับกำหนดสิทธิ์</p>
                                <a href="{{ route('admin.menus') }}" class="inline-flex items-center rounded-md bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">ไปหน้าจัดการเมนู</a>
                            </td>
                        </tr>
                        @endif
                        @foreach ($menus as $menu)
                        @php
                            $perm = $permissions->get($menu->id);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-gray-900">
                                <div>
                                    <div class="font-semibold">{{ $menu->label }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $menu->key }}</div>
                                    @if($menu->department)
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                            {{ $menu->department->label }}
                                        </span>
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
                <button type="submit" class="flex-1 sm:flex-none rounded-lg bg-brand-600 hover:bg-brand-700 px-6 py-3 text-white font-medium shadow-sm transition flex items-center justify-center">
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
    document.querySelectorAll('table input[type="checkbox"]').forEach(cb => cb.checked = true);
});
document.getElementById('deselectAll').addEventListener('click', function() {
    document.querySelectorAll('table input[type="checkbox"]').forEach(cb => cb.checked = false);
});
</script>
@endpush
@endsection
