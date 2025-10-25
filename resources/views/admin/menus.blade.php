@extends('tailadmin.layouts.app')

@section('title', 'จัดการเมนู - ' . config('app.name'))

@push('styles')
<link href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.icon-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    max-height: 200px;
    overflow-y: auto;
}

.icon-item {
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s;
}

.icon-item:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.icon-item.selected {
    border-color: #3b82f6;
    background: #dbeafe;
}

.icon-item svg {
    width: 24px;
    height: 24px;
    margin: 0 auto;
}
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            จัดการเมนูระบบ
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-500 hover:text-brand-500">Dashboard</a></li>
                <li><span class="text-gray-400">/</span></li>
                <li class="font-medium text-brand-500">จัดการเมนู</li>
            </ol>
        </nav>
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

    @if($errors->any())
    <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20">
        <div class="flex items-start">
            <svg class="h-5 w-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-red-700 dark:text-red-400 font-medium mb-1">เกิดข้อผิดพลาด:</p>
                <ul class="list-disc list-inside text-red-600 dark:text-red-400 text-sm">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Add/Edit Form -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-brand-500">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="form-title">เพิ่มเมนูใหม่</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">กรอกข้อมูลเมนูที่ต้องการเพิ่ม</p>
                </div>
            </div>

            <form id="menu-form" onsubmit="return false;" class="space-y-4">
                <input type="hidden" id="menu-id" value="">
                <input type="hidden" id="form-action" value="create">

                <!-- Key -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                        Key <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="key" id="input-key" required
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                        placeholder="dashboard, reports, users" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">รหัสเมนูที่ไม่ซ้ำกัน (ภาษาอังกฤษ)</p>
                </div>

                <!-- Label -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                        ชื่อเมนู <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="label" id="input-label" required
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                        placeholder="แดชบอร์ด, รายงาน, ผู้ใช้งาน" />
                </div>

                <!-- Route -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Route Name</label>
                    <input type="text" name="route" id="input-route"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                        placeholder="tailadmin.dashboard" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ชื่อ route ใน Laravel (ถ้ามี)</p>
                </div>

                <!-- Icon -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Icon</label>
                    <div class="flex gap-2">
                        <input type="text" name="icon" id="input-icon" readonly
                            class="flex-1 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 cursor-pointer dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            placeholder="คลิกเพื่อเลือก icon"
                            onclick="openIconPicker()" />
                        <button type="button" onclick="openIconPicker()"
                            class="rounded-lg bg-purple-600 px-4 py-2.5 text-white hover:bg-purple-700 transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        <button type="button" onclick="clearIcon()"
                            class="rounded-lg bg-gray-400 px-4 py-2.5 text-white hover:bg-gray-500 transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div id="icon-preview" class="mt-2 hidden rounded-lg bg-gray-50 p-3 text-center dark:bg-gray-800">
                        <svg id="icon-preview-svg" class="mx-auto h-8 w-8 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                    </div>
                </div>

                <!-- Parent -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">ประเภทเมนู</label>
                    <select name="parent_id" id="input-parent-id"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                        <option value="">🔹 เมนูหลัก (Main Menu)</option>
                        @foreach($menus as $m)
                            @if(!$m->parent_id)
                            <option value="{{ $m->id }}">└─ เมนูย่อยของ: {{ $m->label }}</option>
                            @endif
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">เลือกเมนูหลักถ้าต้องการสร้าง Sub-menu</p>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">ลำดับการแสดง</label>
                    <input type="number" name="sort_order" id="input-sort-order" value="0"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                        placeholder="0" />
                </div>

                <!-- Menu Group -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">กลุ่มเมนู</label>
                    <select name="menu_group" id="input-menu-group"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                        <option value="default">Default - เมนูทั่วไป</option>
                        <option value="bplus">BPLUS - งบทดลองและระบบบัญชี</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">กลุ่มเมนูสำหรับแยกสิทธิการเข้าถึง</p>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2 pt-2">
                    <button type="submit" id="submit-btn"
                        class="flex-1 rounded-lg bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600 transition flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="submit-text">บันทึกเมนู</span>
                    </button>
                    <button type="button" id="cancel-btn" onclick="resetForm()" style="display:none;"
                        class="rounded-lg bg-gray-500 px-4 py-2.5 text-white hover:bg-gray-600 transition">
                        ยกเลิก
                    </button>
                </div>
            </form>
        </div>

        <!-- Menu List -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">รายการเมนูทั้งหมด</h3>
                    <span class="rounded-full bg-brand-500 px-3 py-1 text-xs font-bold text-white">
                        {{ count($menus) }} เมนู
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">เมนู</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Icon</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">กลุ่ม</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">ลำดับ</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">สถานะ</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($menus as $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $m->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        @if($m->parent_id)
                                            <span class="text-gray-400 mr-1">└─</span>
                                        @endif
                                        {{ $m->label }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $m->key }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($m->icon)
                                    <code class="rounded bg-purple-50 px-2 py-1 text-xs text-purple-700 dark:bg-purple-900/20 dark:text-purple-400">{{ $m->icon }}</code>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ ($m->menu_group ?? 'default') === 'bplus' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' }}">
                                    {{ ($m->menu_group ?? 'default') === 'bplus' ? 'BPLUS' : 'Default' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ $m->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="post" action="{{ route('admin.menus.toggle', $m->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold transition hover:shadow-md
                                        {{ $m->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400' }}">
                                        {{ $m->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='editMenu(@json($m))' class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                        แก้ไข
                                    </button>
                                    @if(!$m->is_system)
                                        <span class="text-gray-300 dark:text-gray-600">|</span>
                                        <form method="post" action="{{ route('admin.menus.destroy', $m->id) }}" class="inline" onsubmit="return confirm('ยืนยันการลบเมนู {{ $m->label }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium text-sm">
                                                ลบ
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">|</span>
                                        <span class="text-gray-400 dark:text-gray-600 text-xs italic">
                                            System
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">ยังไม่มีเมนูในระบบ</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Icon Picker Modal -->
<div id="icon-picker-modal" class="hidden fixed inset-0 bg-black/50 z-[100000] items-center justify-center p-4" onclick="if(event.target === this) closeIconPicker()">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-white">เลือก Icon</h3>
            <button type="button" onclick="closeIconPicker()" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-4">
            <input type="text" id="icon-search" placeholder="ค้นหา icon..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                onkeyup="filterIcons(this.value)">
        </div>

        <div class="px-6 pb-6 overflow-y-auto" style="max-height: 60vh;">
            <div id="icon-grid" class="icon-grid">
                <!-- Icons populated by JS -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
let menusData = []; // Store menus for sort_order calculation

// Icons data
const icons = [
    { name: 'home', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>' },
    { name: 'dashboard', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>' },
    { name: 'users', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>' },
    { name: 'user', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>' },
    { name: 'cog', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' },
    { name: 'chart', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>' },
    { name: 'document', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>' },
    { name: 'folder', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>' },
    { name: 'check', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
    { name: 'credit-card', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>' },
    { name: 'calculator', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>' },
    { name: 'clipboard', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>' },
    { name: 'calendar', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>' },
    { name: 'clock', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
    { name: 'bell', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>' },
    { name: 'mail', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>' },
    { name: 'shopping-cart', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>' },
    { name: 'database', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>' },
    { name: 'server', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>' },
    { name: 'code', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>' },
    { name: 'cube', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>' },
    { name: 'star', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>' },
    { name: 'heart', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>' },
    { name: 'lightning', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>' },
    { name: 'briefcase', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>' },
    { name: 'bookmark', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>' },
    { name: 'camera', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>' },
    { name: 'phone', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>' },
    { name: 'location', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>' },
    { name: 'globe', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
    { name: 'printer', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>' },
    { name: 'download', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>' },
    { name: 'upload', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>' },
    { name: 'search', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>' },
    { name: 'filter', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>' },
    { name: 'refresh', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>' },
    { name: 'trash', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' },
    { name: 'pencil', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>' },
    { name: 'lock', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>' },
    { name: 'unlock', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>' },
    { name: 'shield', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>' },
    { name: 'eye', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>' },
    { name: 'menu', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>' },
    { name: 'dots-vertical', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>' },
    { name: 'plus', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>' },
    { name: 'minus', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>' },
    { name: 'x', svg: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' },
];

// Populate icons
function populateIcons() {
    const grid = document.getElementById('icon-grid');
    grid.innerHTML = icons.map(icon => `
        <button type="button" onclick='selectIcon(${JSON.stringify(icon.name)}, \`${icon.svg}\`)'
            class="icon-item flex flex-col items-center gap-2 rounded-lg border border-gray-200 p-3 transition hover:border-purple-500 hover:bg-purple-50 dark:border-gray-700 dark:hover:border-purple-500 dark:hover:bg-purple-900/20"
            data-icon-name="${icon.name}">
            <svg class="h-8 w-8 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${icon.svg}
            </svg>
            <span class="text-xs text-gray-600 dark:text-gray-400">${icon.name}</span>
        </button>
    `).join('');
}

function openIconPicker() {
    const modal = document.getElementById('icon-picker-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    populateIcons();
}

function closeIconPicker() {
    const modal = document.getElementById('icon-picker-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('icon-search').value = '';
}

function selectIcon(name, svg) {
    document.getElementById('input-icon').value = name;
    const preview = document.getElementById('icon-preview');
    const previewSvg = document.getElementById('icon-preview-svg');
    previewSvg.innerHTML = svg;
    preview.classList.remove('hidden');
    closeIconPicker();
}

function clearIcon() {
    document.getElementById('input-icon').value = '';
    document.getElementById('icon-preview').classList.add('hidden');
}

function filterIcons(query) {
    const items = document.querySelectorAll('.icon-item');
    const lowerQuery = query.toLowerCase();
    items.forEach(item => {
        const name = item.dataset.iconName;
        item.style.display = name.includes(lowerQuery) ? 'flex' : 'none';
    });
}

function editMenu(menu) {
    // Update form title
    document.getElementById('form-title').textContent = 'แก้ไขเมนู';

    // Update form title
    document.getElementById('form-title').textContent = 'แก้ไขเมนู';

    // Set form data
    document.getElementById('menu-id').value = menu.id;
    document.getElementById('form-action').value = 'edit';

    // Fill form fields
    document.getElementById('input-key').value = menu.key || '';
    document.getElementById('input-label').value = menu.label || '';
    document.getElementById('input-route').value = menu.route || '';
    document.getElementById('input-icon').value = menu.icon || '';
    document.getElementById('input-parent-id').value = menu.parent_id || '';
    document.getElementById('input-sort-order').value = menu.sort_order || 0;
    document.getElementById('input-menu-group').value = menu.menu_group || 'default';

    // Show icon preview if exists
    if (menu.icon) {
        const iconData = icons.find(i => i.name === menu.icon);
        if (iconData) {
            const preview = document.getElementById('icon-preview');
            const previewSvg = document.getElementById('icon-preview-svg');
            previewSvg.innerHTML = iconData.svg;
            preview.classList.remove('hidden');
        }
    }

    // Update button text
    document.getElementById('submit-text').textContent = 'อัปเดตเมนู';
    document.getElementById('cancel-btn').style.display = 'block';

    // Scroll to form
    document.getElementById('menu-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetForm() {
    // Reset form title
    document.getElementById('form-title').textContent = 'เพิ่มเมนูใหม่';

    // Clear form fields
    document.getElementById('menu-id').value = '';
    document.getElementById('form-action').value = 'create';
    document.getElementById('input-key').value = '';
    document.getElementById('input-label').value = '';
    document.getElementById('input-route').value = '';
    document.getElementById('input-icon').value = '';
    document.getElementById('input-parent-id').value = '';
    document.getElementById('input-menu-group').value = 'default';

    // Auto-increment sort_order
    calculateNextSortOrder();

    // Hide icon preview
    document.getElementById('icon-preview').classList.add('hidden');

    // Reset button text
    document.getElementById('submit-text').textContent = 'บันทึกเมนู';
    document.getElementById('cancel-btn').style.display = 'none';
}

// Calculate next sort_order (max + 1)
function calculateNextSortOrder() {
    const maxOrder = menusData.reduce((max, menu) => Math.max(max, menu.sort_order || 0), 0);
    document.getElementById('input-sort-order').value = maxOrder + 1;
}

// Load menus data via AJAX
function loadMenusData() {
    fetch('/admin/menus/list', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            menusData = data.data || [];
            calculateNextSortOrder();
        }
    })
    .catch(error => {
        console.error('Error loading menus:', error);
    });
}

// Toast notification
function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-[10000] animate-fade-in`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.remove();
}

// Submit form via AJAX
document.getElementById('menu-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const menuId = document.getElementById('menu-id').value;
    const formAction = document.getElementById('form-action').value;

    const formData = {
        key: document.getElementById('input-key').value,
        label: document.getElementById('input-label').value,
        route: document.getElementById('input-route').value || null,
        icon: document.getElementById('input-icon').value || null,
        parent_id: document.getElementById('input-parent-id').value || null,
        sort_order: parseInt(document.getElementById('input-sort-order').value) || 0,
        menu_group: document.getElementById('input-menu-group').value || 'default',
    };

    const url = formAction === 'edit' ? `/admin/menus/${menuId}` : '/admin/menus';
    const method = formAction === 'edit' ? 'PUT' : 'POST';

    showLoading();

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(formData),
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();

        if (data.success) {
            showToast(data.message || 'บันทึกสำเร็จ', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('เกิดข้อผิดพลาดในการบันทึก', 'error');
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadMenusData();
    calculateNextSortOrder();
});
</script>
@endpush

@endsection
