@extends('tailadmin.layouts.app')

@section('title', 'รายงานการพิมพ์เช็ค - ' . config('app.name'))

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
.dataTables_wrapper {
    padding: 20px;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    margin-left: 8px;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 12px;
    margin: 0 8px;
}

table.dataTable {
    border-collapse: collapse !important;
}

table.dataTable thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    padding: 12px;
}

table.dataTable tbody td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

table.dataTable tbody tr:hover {
    background: #f8f9fa;
}

.dark table.dataTable thead th {
    background: #374151;
    color: #fff;
    border-bottom-color: #4b5563;
}

.dark table.dataTable tbody td {
    border-bottom-color: #4b5563;
    color: #d1d5db;
}

.dark table.dataTable tbody tr:hover {
    background: #374151;
}

.stats-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e5e7eb;
}

.dark .stats-card {
    background: #1f2937;
    border-color: #374151;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #1f2937;
}

.dark .stat-value {
    color: #f9fafb;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 4px;
}

.dark .stat-label {
    color: #9ca3af;
}
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            📊 รายงานการพิมพ์เช็ค
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('cheque.print') }}">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">รายงาน</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="stats-card">
            <div class="stat-value" id="total-cheques">0</div>
            <div class="stat-label">เช็คทั้งหมด</div>
        </div>
        <div class="stats-card">
            <div class="stat-value text-blue-600 dark:text-blue-400" id="total-amount">฿0</div>
            <div class="stat-label">มูลค่ารวม</div>
        </div>
        <div class="stats-card">
            <div class="stat-value text-green-600 dark:text-green-400" id="today-cheques">0</div>
            <div class="stat-label">พิมพ์วันนี้</div>
        </div>
        <div class="stats-card">
            <div class="stat-value text-purple-600 dark:text-purple-400" id="unique-payees">0</div>
            <div class="stat-label">ผู้รับเงินทั้งหมด</div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">รายการเช็คทั้งหมด</h3>
                <div class="flex gap-2">
                    <button onclick="exportToExcel()" class="rounded bg-green-500 px-4 py-2 text-sm text-white hover:bg-green-600">
                        📥 Export Excel
                    </button>
                    <button onclick="refreshData()" class="rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        🔄 รีเฟรช
                    </button>
                </div>
            </div>

            <table id="cheques-table" class="display w-full">
                <thead>
                    <tr>
                        <th>รหัสสาขา</th>
                        <th>ธนาคาร</th>
                        <th>เลขที่เช็ค</th>
                        <th>วันที่</th>
                        <th>ผู้รับเงิน</th>
                        <th>จำนวนเงิน</th>
                        <th>พิมพ์เมื่อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('cheque.print') }}" class="inline-flex items-center gap-2 rounded bg-gray-100 px-6 py-3 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            ← กลับหน้าพิมพ์เช็ค
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '/api';
let chequesTable;

$(document).ready(function() {
    initializeDataTable();
    updateStats();
});

function initializeDataTable() {
    chequesTable = $('#cheques-table').DataTable({
        ajax: {
            url: `${API_BASE}/cheques`,
            dataSrc: ''
        },
        columns: [
            { data: 'branch_code' },
            { data: 'bank' },
            { data: 'cheque_number' },
            {
                data: 'date',
                render: function(data, type, row) {
                    // For sorting, use raw data (ISO format)
                    if (type === 'sort' || type === 'type') {
                        return data || '';
                    }
                    // For display, format as Thai date
                    if (!data) return '-';
                    const date = new Date(data);
                    const day = date.getDate();
                    const month = date.getMonth() + 1;
                    const year = date.getFullYear() + 543; // Thai year
                    return `${day}/${month}/${year}`;
                }
            },
            { data: 'payee' },
            {
                data: 'amount',
                render: function(data, type, row) {
                    // For sorting, use numeric value
                    if (type === 'sort' || type === 'type') {
                        return parseFloat(data) || 0;
                    }
                    // For display, format with currency
                    return '฿' + parseFloat(data).toLocaleString('th-TH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            {
                data: 'printed_at',
                render: function(data, type, row) {
                    // For sorting, use raw data (ISO format)
                    if (type === 'sort' || type === 'type') {
                        return data || '';
                    }
                    // For display, format as Thai datetime
                    if (!data) return '-';
                    const date = new Date(data);
                    const day = date.getDate();
                    const month = date.getMonth() + 1;
                    const year = date.getFullYear() + 543; // Thai year
                    const hours = date.getHours().toString().padStart(2, '0');
                    const minutes = date.getMinutes().toString().padStart(2, '0');
                    return `${day}/${month}/${year} ${hours}:${minutes}`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button onclick="deleteCheque(${row.id})" class="text-red-500 hover:text-red-700" title="ลบ">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    `;
                }
            }
        ],
        order: [[6, 'desc']], // Sort by printed_at descending
        deferRender: true, // Optimize rendering for large datasets
        pageLength: 25,
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "ไม่มีข้อมูล",
            infoFiltered: "(กรองจาก _MAX_ รายการทั้งหมด)",
            paginate: {
                first: "แรก",
                last: "สุดท้าย",
                next: "ถัดไป",
                previous: "ก่อนหน้า"
            },
            zeroRecords: "ไม่พบข้อมูลที่ค้นหา",
            emptyTable: "ยังไม่มีข้อมูลการพิมพ์เช็ค"
        }
    });
}

async function updateStats() {
    try {
        const response = await fetch(`${API_BASE}/cheques`);
        const cheques = await response.json();

        // Total cheques
        document.getElementById('total-cheques').textContent = cheques.length;

        // Total amount
        const totalAmount = cheques.reduce((sum, cheque) => sum + parseFloat(cheque.amount || 0), 0);
        document.getElementById('total-amount').textContent = '฿' + totalAmount.toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Today's cheques
        const today = new Date().toISOString().split('T')[0];
        const todayCheques = cheques.filter(c => {
            if (!c.printed_at) return false;
            const printedDate = new Date(c.printed_at).toISOString().split('T')[0];
            return printedDate === today;
        });
        document.getElementById('today-cheques').textContent = todayCheques.length;

        // Unique payees
        const uniquePayees = new Set(cheques.map(c => c.payee).filter(p => p));
        document.getElementById('unique-payees').textContent = uniquePayees.size;

    } catch (error) {
        console.error('Error updating stats:', error);
    }
}

async function deleteCheque(id) {
    const result = await Swal.fire({
        title: 'ต้องการลบเช็คนี้?',
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
            const response = await fetch(`${API_BASE}/cheques/${id}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                Swal.fire({
                    title: 'ลบสำเร็จ!',
                    text: 'ลบข้อมูลเช็คแล้ว',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                chequesTable.ajax.reload();
                updateStats();
            } else {
                throw new Error('Delete failed');
            }
        } catch (error) {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: 'ไม่สามารถลบข้อมูลได้',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    }
}

function refreshData() {
    chequesTable.ajax.reload();
    updateStats();
    Swal.fire({
        title: 'รีเฟรชสำเร็จ!',
        icon: 'success',
        timer: 1000,
        showConfirmButton: false
    });
}

function exportToExcel() {
    // Get all data from table
    const data = chequesTable.rows().data().toArray();

    if (data.length === 0) {
        Swal.fire({
            title: 'ไม่มีข้อมูล!',
            text: 'ยังไม่มีข้อมูลการพิมพ์เช็คสำหรับ Export',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    // Create CSV content
    let csvContent = 'data:text/csv;charset=utf-8,\uFEFF';
    csvContent += 'รหัสสาขา,ธนาคาร,เลขที่เช็ค,วันที่,ผู้รับเงิน,จำนวนเงิน,พิมพ์เมื่อ\n';

    data.forEach(row => {
        const date = row.date ? new Date(row.date).toLocaleDateString('th-TH') : '';
        const printed = row.printed_at ? new Date(row.printed_at).toLocaleString('th-TH') : '';
        const amount = parseFloat(row.amount || 0).toFixed(2);

        csvContent += `"${row.branch_code}","${row.bank}","${row.cheque_number}","${date}","${row.payee}","${amount}","${printed}"\n`;
    });

    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `cheque-report-${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    Swal.fire({
        title: 'Export สำเร็จ!',
        text: 'ดาวน์โหลดไฟล์เรียบร้อยแล้ว',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}
</script>
@endpush
