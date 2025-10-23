@extends('tailadmin.layouts.app')

@section('title', 'จัดการสาขา - ระบบเช็ค')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">จัดการสาขา</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">Dashboard /</a></li>
                <li><a href="{{ route('cheque.print') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">จัดการสาขา</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: Form -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white" id="form-title">เพิ่มสาขาใหม่</h3>

                <form id="branch-form" class="space-y-4" onsubmit="saveBranch(event)">
                    <input type="hidden" id="branch-id" />

                    <div>
                        <label class="mb-2 block text-sm font-medium">รหัสสาขา <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="branch-code"
                            required
                            placeholder="เช่น HQ, BKK01"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ชื่อสาขา <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="branch-name"
                            required
                            placeholder="เช่น สำนักงานใหญ่"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ที่อยู่</label>
                        <textarea
                            id="branch-address"
                            rows="3"
                            placeholder="ที่อยู่สาขา (ไม่บังคับ)"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500"
                        ></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">เบอร์โทรศัพท์</label>
                        <input
                            type="tel"
                            id="branch-phone"
                            placeholder="เช่น 02-123-4567"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="branch-active"
                            checked
                            class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        />
                        <label for="branch-active" class="text-sm font-medium">เปิดใช้งาน</label>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            class="flex-1 rounded bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600"
                        >
                            บันทึก
                        </button>
                        <button
                            type="button"
                            onclick="resetForm()"
                            class="rounded border border-gray-300 px-4 py-2.5 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800"
                        >
                            ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Branch List -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">รายการสาขา</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            ทั้งหมด <span id="total-branches" class="font-semibold">0</span> สาขา
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Search -->
                    <div class="mb-4">
                        <input
                            type="text"
                            id="search-branch"
                            placeholder="ค้นหาสาขา..."
                            onkeyup="filterBranches()"
                            class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700"
                        />
                    </div>

                    <!-- Branches Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                    <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">รหัส</th>
                                    <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">ชื่อสาขา</th>
                                    <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">เบอร์โทร</th>
                                    <th class="px-4 py-3 font-medium text-gray-900 dark:text-white text-center">สถานะ</th>
                                    <th class="px-4 py-3 font-medium text-gray-900 dark:text-white text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="branches-list">
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        กำลังโหลดข้อมูล...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let branches = [];
let editingId = null;

// Load all branches
async function loadBranches() {
    try {
        const response = await axios.get('/api/branches');
        branches = response.data;
        displayBranches();
    } catch (error) {
        console.error('Failed to load branches:', error);
        document.getElementById('branches-list').innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
    }
}

// Display branches in table
function displayBranches(filteredBranches = null) {
    const data = filteredBranches || branches;
    const tbody = document.getElementById('branches-list');
    document.getElementById('total-branches').textContent = data.length;

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">ไม่พบข้อมูลสาขา</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(branch => `
        <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
            <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold">${branch.code}</td>
            <td class="px-4 py-3">
                <div class="text-gray-900 dark:text-white font-medium">${branch.name}</div>
                ${branch.address ? `<div class="text-xs text-gray-500 dark:text-gray-400 mt-1">${branch.address}</div>` : ''}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">${branch.phone || '-'}</td>
            <td class="px-4 py-3 text-center">
                ${branch.is_active
                    ? '<span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">เปิดใช้งาน</span>'
                    : '<span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">ปิดใช้งาน</span>'
                }
            </td>
            <td class="px-4 py-3">
                <div class="flex justify-center gap-2">
                    <button onclick="editBranch(${branch.id})" class="text-blue-500 hover:text-blue-700" title="แก้ไข">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button onclick="deleteBranch(${branch.id}, '${branch.name}')" class="text-red-500 hover:text-red-700" title="ลบ">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Filter branches by search
function filterBranches() {
    const search = document.getElementById('search-branch').value.toLowerCase();
    const filtered = branches.filter(b =>
        b.code.toLowerCase().includes(search) ||
        b.name.toLowerCase().includes(search) ||
        (b.phone && b.phone.includes(search))
    );
    displayBranches(filtered);
}

// Save branch (create or update)
async function saveBranch(event) {
    event.preventDefault();

    const data = {
        code: document.getElementById('branch-code').value,
        name: document.getElementById('branch-name').value,
        address: document.getElementById('branch-address').value,
        phone: document.getElementById('branch-phone').value,
        is_active: document.getElementById('branch-active').checked
    };

    try {
        if (editingId) {
            // Update
            await axios.put(`/api/branches/${editingId}`, data);
            alert('อัพเดทสาขาเรียบร้อยแล้ว');
        } else {
            // Create
            await axios.post('/api/branches', data);
            alert('เพิ่มสาขาใหม่เรียบร้อยแล้ว');
        }

        resetForm();
        loadBranches();
    } catch (error) {
        console.error('Save failed:', error);
        alert('เกิดข้อผิดพลาด: ' + (error.response?.data?.message || error.message));
    }
}

// Edit branch
function editBranch(id) {
    const branch = branches.find(b => b.id === id);
    if (!branch) return;

    editingId = id;
    document.getElementById('form-title').textContent = 'แก้ไขข้อมูลสาขา';
    document.getElementById('branch-id').value = branch.id;
    document.getElementById('branch-code').value = branch.code;
    document.getElementById('branch-name').value = branch.name;
    document.getElementById('branch-address').value = branch.address || '';
    document.getElementById('branch-phone').value = branch.phone || '';
    document.getElementById('branch-active').checked = branch.is_active;

    // Scroll to form
    document.getElementById('branch-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Delete branch
async function deleteBranch(id, name) {
    if (!confirm(`คุณต้องการลบสาขา "${name}" ใช่หรือไม่?`)) {
        return;
    }

    try {
        await axios.delete(`/api/branches/${id}`);
        alert('ลบสาขาเรียบร้อยแล้ว');
        loadBranches();
    } catch (error) {
        console.error('Delete failed:', error);
        alert('ไม่สามารถลบสาขาได้: ' + (error.response?.data?.message || error.message));
    }
}

// Reset form
function resetForm() {
    editingId = null;
    document.getElementById('form-title').textContent = 'เพิ่มสาขาใหม่';
    document.getElementById('branch-form').reset();
    document.getElementById('branch-id').value = '';
    document.getElementById('branch-active').checked = true;
}

// Initialize
window.addEventListener('DOMContentLoaded', () => {
    loadBranches();
});
</script>
@endpush
