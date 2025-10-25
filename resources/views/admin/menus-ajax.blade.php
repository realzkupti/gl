@extends('tailadmin.layouts.app')

@section('title', 'จัดการเมนู - ' . config('app.name'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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

            <form id="menu-form" class="space-y-4" onsubmit="return false;">
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
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Icon Class</label>
                    <input type="text" name="icon" id="input-icon"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white"
                        placeholder="heroicon-s-home" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">ชื่อ class ของ icon (ถ้ามี)</p>
                </div>

                <!-- Parent Menu -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">เมนูหลัก</label>
                    <select name="parent_id" id="input-parent"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                        <option value="">-- ไม่มี (เป็นเมนูหลัก) --</option>
                    </select>
                </div>

                <!-- Menu Group -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">กลุ่มเมนู</label>
                    <select name="menu_group_id" id="input-menu-group"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                        <option value="">-- ไม่มีกลุ่ม --</option>
                    </select>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">ลำดับการแสดง</label>
                    <input type="number" name="sort_order" id="input-sort" value="0"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="input-active" checked
                        class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                    <label for="input-active" class="text-sm font-medium text-gray-900 dark:text-white">
                        เปิดใช้งาน
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="saveMenu()" class="flex-1 rounded-lg bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                        💾 บันทึก
                    </button>
                    <button type="button" onclick="resetForm()" class="rounded-lg border border-gray-300 px-6 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        🔄 ล้างฟอร์ม
                    </button>
                </div>
            </form>
        </div>

        <!-- Menu List -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-500">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">รายการเมนู</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">ทั้งหมด <span id="menu-count">0</span> เมนู</p>
                    </div>
                </div>
                <button onclick="loadMenus()" class="rounded-lg bg-gray-100 px-4 py-2 text-sm hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700">
                    🔄 รีเฟรช
                </button>
            </div>

            <div id="menu-list" class="space-y-2">
                <!-- Menus will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="spinner"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ==================== CONFIGURATION ====================
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const BASE_URL = '/admin/menus/api';
const LIST_URL = '/admin/menus/list';

// ==================== UTILITY FUNCTIONS ====================

// Show/Hide Loading
function showLoading() {
    document.getElementById('loading-overlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

// Show Toast Message
function showToast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// ==================== API CALLS (using fetch) ====================

// Load all menus
async function loadMenus() {
    try {
        showLoading();
        const response = await fetch(LIST_URL, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load menus');
        }

        const data = await response.json();
        renderMenus(data.menus);
        updateParentOptions(data.menus);
        document.getElementById('menu-count').textContent = data.menus.length;
    } catch (error) {
        console.error('Error loading menus:', error);
        showToast('ไม่สามารถโหลดข้อมูลเมนูได้', 'error');
    } finally {
        hideLoading();
    }
}

// Load menu groups
async function loadMenuGroups() {
    try {
        const response = await fetch('/admin/menu-groups/list', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        if (response.ok) {
            const data = await response.json();
            const select = document.getElementById('input-menu-group');
            select.innerHTML = '<option value="">-- ไม่มีกลุ่ม --</option>';
            data.menuGroups.forEach(group => {
                select.innerHTML += `<option value="${group.id}">${group.label}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading menu groups:', error);
    }
}

// Save menu (Create or Update)
async function saveMenu() {
    const formAction = document.getElementById('form-action').value;
    const menuId = document.getElementById('menu-id').value;

    const formData = {
        key: document.getElementById('input-key').value.trim(),
        label: document.getElementById('input-label').value.trim(),
        route: document.getElementById('input-route').value.trim() || null,
        icon: document.getElementById('input-icon').value.trim() || null,
        parent_id: document.getElementById('input-parent').value || null,
        menu_group_id: document.getElementById('input-menu-group').value || null,
        sort_order: parseInt(document.getElementById('input-sort').value) || 0,
        is_active: document.getElementById('input-active').checked
    };

    // Validation
    if (!formData.key || !formData.label) {
        showToast('กรุณากรอก Key และชื่อเมนู', 'warning');
        return;
    }

    try {
        showLoading();

        const url = formAction === 'create' ? BASE_URL : `${BASE_URL}/${menuId}`;
        const method = formAction === 'create' ? 'POST' : 'PUT';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (!response.ok) {
            // Show validation errors
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                showToast(errorMessages, 'error');
            } else {
                throw new Error(data.message || 'Failed to save menu');
            }
            return;
        }

        showToast(data.message || 'บันทึกเมนูสำเร็จ', 'success');
        resetForm();
        loadMenus();

    } catch (error) {
        console.error('Error saving menu:', error);
        showToast('เกิดข้อผิดพลาดในการบันทึก', 'error');
    } finally {
        hideLoading();
    }
}

// Delete menu
async function deleteMenu(id, label) {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `ต้องการลบเมนู "${label}" หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    });

    if (!result.isConfirmed) return;

    try {
        showLoading();

        const response = await fetch(`${BASE_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to delete menu');
        }

        showToast(data.message || 'ลบเมนูสำเร็จ', 'success');
        loadMenus();

    } catch (error) {
        console.error('Error deleting menu:', error);
        showToast(error.message || 'ไม่สามารถลบเมนูได้', 'error');
    } finally {
        hideLoading();
    }
}

// Toggle menu active status
async function toggleMenu(id, currentStatus) {
    try {
        const response = await fetch(`${BASE_URL}/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to toggle menu');
        }

        showToast(data.message || 'อัปเดตสถานะสำเร็จ', 'success');
        loadMenus();

    } catch (error) {
        console.error('Error toggling menu:', error);
        showToast('ไม่สามารถเปลี่ยนสถานะได้', 'error');
    }
}

// ==================== UI FUNCTIONS ====================

// Edit menu
function editMenu(menu) {
    document.getElementById('form-title').textContent = 'แก้ไขเมนู';
    document.getElementById('form-action').value = 'update';
    document.getElementById('menu-id').value = menu.id;

    document.getElementById('input-key').value = menu.key;
    document.getElementById('input-label').value = menu.label;
    document.getElementById('input-route').value = menu.route || '';
    document.getElementById('input-icon').value = menu.icon || '';
    document.getElementById('input-parent').value = menu.parent_id || '';
    document.getElementById('input-menu-group').value = menu.menu_group_id || '';
    document.getElementById('input-sort').value = menu.sort_order || 0;
    document.getElementById('input-active').checked = menu.is_active;

    // Scroll to form
    document.getElementById('menu-form').scrollIntoView({ behavior: 'smooth' });
}

// Reset form
function resetForm() {
    document.getElementById('form-title').textContent = 'เพิ่มเมนูใหม่';
    document.getElementById('form-action').value = 'create';
    document.getElementById('menu-id').value = '';
    document.getElementById('menu-form').reset();
    document.getElementById('input-active').checked = true;
}

// Render menus
function renderMenus(menus) {
    const container = document.getElementById('menu-list');

    if (!menus || menus.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="h-16 w-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>ไม่มีข้อมูลเมนู</p>
            </div>
        `;
        return;
    }

    container.innerHTML = menus.map(menu => `
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-900 dark:text-white">${menu.label}</span>
                        <span class="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">${menu.key}</span>
                        ${menu.is_active ?
                            '<span class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>' :
                            '<span class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700 dark:bg-red-900/30 dark:text-red-400">Inactive</span>'
                        }
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        ${menu.route ? `<span>Route: ${menu.route}</span>` : ''}
                        ${menu.parent_id ? `<span class="ml-2">Parent: #${menu.parent_id}</span>` : ''}
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick='editMenu(${JSON.stringify(menu)})' class="rounded p-2 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20" title="แก้ไข">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="toggleMenu(${menu.id}, ${menu.is_active})" class="rounded p-2 text-orange-600 hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-900/20" title="เปิด/ปิดใช้งาน">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </button>
                    ${!menu.is_system ? `
                        <button onclick="deleteMenu(${menu.id}, '${menu.label}')" class="rounded p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20" title="ลบ">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    ` : '<div class="w-10"></div>'}
                </div>
            </div>
        </div>
    `).join('');
}

// Update parent menu options
function updateParentOptions(menus) {
    const select = document.getElementById('input-parent');
    const currentId = document.getElementById('menu-id').value;

    select.innerHTML = '<option value="">-- ไม่มี (เป็นเมนูหลัก) --</option>';

    menus.forEach(menu => {
        // Don't allow selecting self as parent
        if (menu.id != currentId) {
            select.innerHTML += `<option value="${menu.id}">${menu.label} (${menu.key})</option>`;
        }
    });
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    loadMenus();
    loadMenuGroups();
});
</script>
@endpush
