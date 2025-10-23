@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-7xl px-4 py-8">
    <!-- Header with Back Button -->
    <div class="mb-8">
        <a href="{{ route('admin.user-permissions') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            กลับไปหน้ารายการผู้ใช้
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">จัดการสิทธิ์: {{ $user->name }}</h1>
                <p class="text-gray-600">{{ $user->email }}</p>
            </div>
            <div class="flex-shrink-0 h-16 w-16">
                <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-700 font-bold text-xl">{{ $user->initials() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('status'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-green-700 font-medium">{{ session('status') }}</p>
        </div>
    </div>
    @endif

    <!-- Permission Form -->
    <form method="POST" action="{{ route('admin.user-permissions.update', $user->id) }}">
        @csrf
        @method('PUT')

        <!-- Action Buttons (Top) -->
        <div class="mb-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                เลือกสิทธิ์สำหรับแต่ละเมนู (ถ้าไม่เลือก = ไม่มีสิทธิ์)
            </div>
            <div class="flex gap-2">
                <button type="button" id="selectAll" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-sm transition">
                    เลือกทั้งหมด
                </button>
                <button type="button" id="deselectAll" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-sm transition">
                    ยกเลิกทั้งหมด
                </button>
            </div>
        </div>

        <!-- Permissions Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">
                                เมนู
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>ดู</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>สร้าง</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>แก้ไข</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>ลบ</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Export</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>อนุมัติ</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($menus as $menu)
                        @php
                            $perm = $userPermissions->get($menu->id);
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white">
                                <div>
                                    <div class="font-semibold">{{ $menu->name_th }}</div>
                                    <div class="text-xs text-gray-500">{{ $menu->key }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="hidden" name="permissions[{{ $loop->index }}][menu_id]" value="{{ $menu->id }}">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_view]" value="1"
                                    {{ $perm && $perm->can_view ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_create]" value="1"
                                    {{ $perm && $perm->can_create ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_update]" value="1"
                                    {{ $perm && $perm->can_update ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_delete]" value="1"
                                    {{ $perm && $perm->can_delete ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_export]" value="1"
                                    {{ $perm && $perm->can_export ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="permissions[{{ $loop->index }}][can_approve]" value="1"
                                    {{ $perm && $perm->can_approve ? 'checked' : '' }}
                                    class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons (Bottom) -->
        <div class="flex justify-between items-center">
            <button type="button" onclick="if(confirm('ยืนยันการล้างสิทธิ์ทั้งหมด?')) { document.getElementById('resetForm').submit(); }"
                class="px-6 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-md transition">
                ล้างสิทธิ์ทั้งหมด
            </button>
            <div class="flex gap-3">
                <a href="{{ route('admin.user-permissions') }}" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-md transition">
                    ยกเลิก
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm transition flex items-center">
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

<script>
document.getElementById('selectAll').addEventListener('click', function() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
});
document.getElementById('deselectAll').addEventListener('click', function() {
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
});
</script>
@endsection
