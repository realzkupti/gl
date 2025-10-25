@extends('tailadmin.layouts.app')

@section('title', 'จัดการสาขา - ' . config('app.name'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            🏢 จัดการสาขา
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('cheque.print') }}">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">จัดการสาขา</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: Form (1 column) -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">➕ เพิ่มสาขาใหม่</h3>

                <form id="branch_form" onsubmit="saveBranch(event)" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">รหัสสาขา <span class="text-red-500">*</span></label>
                        <input type="text" id="branch_code" required placeholder="เช่น HQ, BKK01"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">รหัสสำหรับระบุสาขา (ไม่ซ้ำกัน)</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">ชื่อสาขา <span class="text-red-500">*</span></label>
                        <input type="text" id="branch_name" required placeholder="เช่น สำนักงานใหญ่"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                    </div>

                    <button type="submit" class="w-full rounded bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600">
                        💾 บันทึกสาขา
                    </button>
                </form>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <a href="{{ route('cheque.print') }}" class="block w-full rounded bg-gray-100 px-4 py-2.5 text-center text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        ← กลับหน้าพิมพ์เช็ค
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: List (2 columns) -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📋 รายการสาขาทั้งหมด</h3>
                    <button onclick="loadBranches()" class="rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        🔄 รีเฟรช
                    </button>
                </div>

                <div id="branches_list" class="space-y-3">
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        กำลังโหลด...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '/api';

// Load branches on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
});

// Load branches list
async function loadBranches() {
    try {
        const response = await fetch(`${API_BASE}/branches`);
        const branches = await response.json();

        const listDiv = document.getElementById('branches_list');

        if (branches.length === 0) {
            listDiv.innerHTML = `
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <p class="text-lg">ยังไม่มีสาขา</p>
                    <p class="text-sm mt-2">เพิ่มสาขาแรกของคุณทางซ้ายมือ</p>
                </div>
            `;
            return;
        }

        listDiv.innerHTML = branches.map(branch => `
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">${branch.name}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">รหัส: ${branch.code}</div>
                </div>
                <button onclick="deleteBranch('${branch.code}')" class="rounded bg-red-500 px-3 py-2 text-sm text-white hover:bg-red-600" title="ลบสาขา">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading branches:', error);
        document.getElementById('branches_list').innerHTML = `
            <div class="text-center text-red-500 py-8">
                เกิดข้อผิดพลาดในการโหลดข้อมูล
            </div>
        `;
    }
}

// Save branch
async function saveBranch(event) {
    event.preventDefault();

    const code = document.getElementById('branch_code').value.trim();
    const name = document.getElementById('branch_name').value.trim();

    if (!code || !name) {
        Swal.fire({
            title: 'ข้อมูลไม่ครบถ้วน!',
            text: 'กรุณากรอกข้อมูลให้ครบทุกช่อง',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/branches`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code, name })
        });

        const data = await response.json();

        if (response.ok) {
            Swal.fire({
                title: 'บันทึกสำเร็จ!',
                text: 'เพิ่มสาขาใหม่เรียบร้อยแล้ว',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

            // Clear form
            document.getElementById('branch_form').reset();

            // Reload list
            loadBranches();
        } else if (response.status === 409) {
            Swal.fire({
                title: 'รหัสสาขาซ้ำ!',
                text: 'มีรหัสสาขานี้อยู่ในระบบแล้ว กรุณาใช้รหัสอื่น',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        } else {
            throw new Error('Save failed');
        }
    } catch (error) {
        console.error('Error saving branch:', error);
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่สามารถบันทึกข้อมูลได้',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Delete branch
async function deleteBranch(code) {
    const result = await Swal.fire({
        title: 'ต้องการลบสาขานี้?',
        text: 'การกระทำนี้ไม่สามารถย้อนกลับได้',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`${API_BASE}/branches/${encodeURIComponent(code)}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                Swal.fire({
                    title: 'ลบสำเร็จ!',
                    text: 'ลบสาขาเรียบร้อยแล้ว',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                loadBranches();
            } else {
                throw new Error('Delete failed');
            }
        } catch (error) {
            console.error('Error deleting branch:', error);
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: 'ไม่สามารถลบข้อมูลได้',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    }
}
</script>
@endpush
