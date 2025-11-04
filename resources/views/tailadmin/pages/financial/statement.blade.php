@extends('tailadmin.layouts.app')

@section('title', 'Bank Statement')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
<style>
    /* Dark mode สำหรับ DataTables */
    .dark .dataTables_wrapper { color: #e5e7eb; }
    .dark table.dataTable thead th, .dark table.dataTable thead td {
        color: #e5e7eb;
        border-bottom-color: #374151;
    }
    .dark table.dataTable tbody td {
        color: #d1d5db;
        border-bottom-color: #374151;
    }
    .dark table.dataTable.stripe tbody tr.odd { background-color: #1f2937; }
    .dark table.dataTable.hover tbody tr:hover { background-color: #374151; }
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button { color: #9ca3af !important; }
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        color: #fff !important;
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .dark .dataTables_wrapper .dataTables_length select,
    .dark .dataTables_wrapper .dataTables_filter input {
        background-color: #374151;
        color: #e5e7eb;
        border-color: #4b5563;
    }

    /* Card borders ชัดเจนขึ้น */
    .card-white {
        border: 2px solid #E5E7EB;
        background-color: #FFFFFF;
    }

    .dark .card-white {
        border: 2px solid #374151;
        background-color: #1A222C;
    }

    .card-gradient {
        border: 2px solid #E5E7EB;
    }

    .dark .card-gradient {
        border: 2px solid #374151;
    }

    /* Select dropdown dark mode - เพิ่มความชัด */
    .dark select {
        background-color: #24303F;
        color: #DEE4EE;
        border-color: #3E4954;
    }

    .dark select option {
        background-color: #24303F;
        color: #DEE4EE;
    }

    .dark select:focus {
        border-color: #3C50E0;
        background-color: #24303F;
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Bank Statement
        </h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-primary" href="{{ route('financial.dashboard') }}">Financial Dashboard /</a>
                </li>
                <li class="font-medium text-gray-700 dark:text-gray-300">Bank Statement</li>
            </ol>
        </nav>
    </div>

    <!-- Company Info -->
    <div class="mb-6 rounded-lg px-7.5 py-4 shadow-default card-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-black dark:text-white">
                    {{ $company->label }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Company Code: {{ $company->key }}
                </p>
            </div>
            <a href="{{ route('financial.dashboard') }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-4 py-2 text-center font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                กลับ
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg px-7.5 py-6 shadow-default card-white">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Account Selector -->
            <div>
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    เลือกบัญชี
                </label>
                <select id="account-select"
                    class="w-full rounded border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                    <option value="">-- เลือกบัญชี --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->BNKAC_KEY }}"
                            {{ ($accountKey ?? '') == $acc->BNKAC_KEY ? 'selected' : '' }}>
                            {{ $acc->BNKAC_CODE }} - {{ $acc->BNKAC_NAME }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Year Selector -->
            <div>
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    ปี
                </label>
                <select id="year-select"
                    class="w-full rounded border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y + 543 }}</option>
                    @endfor
                </select>
            </div>

            <!-- Month Selector -->
            <div>
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    เดือน
                </label>
                <select id="month-select"
                    class="w-full rounded border border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary dark:border-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                    @php
                        $thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                                       'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                    @endphp
                    @foreach($thaiMonths as $m => $monthName)
                        <option value="{{ $m + 1 }}" {{ $month == ($m + 1) ? 'selected' : '' }}>{{ $monthName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Load Button -->
            <div class="flex items-end">
                <button onclick="bankStatement.loadStatement()"
                    class="w-full inline-flex items-center justify-center rounded-md bg-blue-600 dark:bg-blue-700 px-6 py-3 text-center font-medium text-white hover:bg-blue-700 dark:hover:bg-blue-600">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    แสดงข้อมูล
                </button>
            </div>
        </div>
    </div>

    <!-- Account Info Card -->
    <div id="account-info" class="hidden mb-6 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 px-7.5 py-6 shadow-default card-gradient">
        <div class="mb-4">
            <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-200">
                <span id="account-code"></span> - <span id="account-name"></span>
            </h4>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                <span id="period-text"></span>
            </p>
        </div>

        <!-- Summary Grid -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center">
                <p class="text-xs text-blue-700 dark:text-blue-300 mb-1">จำนวนรายการ</p>
                <p class="text-lg font-bold text-blue-900 dark:text-blue-100">
                    <span id="trans-count">0</span>
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-blue-700 dark:text-blue-300 mb-1">ยอดยกมา</p>
                <p class="text-lg font-bold text-blue-900 dark:text-blue-100">
                    <span id="carry-over">0.00</span>
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-green-700 dark:text-green-300 mb-1">ฝากรวม</p>
                <p class="text-lg font-bold text-green-700 dark:text-green-300">
                    <span id="total-debit">0.00</span>
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-red-700 dark:text-red-300 mb-1">ถอนรวม</p>
                <p class="text-lg font-bold text-red-700 dark:text-red-300">
                    <span id="total-credit">0.00</span>
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-blue-700 dark:text-blue-300 mb-1">ยอดคงเหลือ</p>
                <p class="text-lg font-bold text-blue-900 dark:text-blue-100">
                    <span id="ending-balance">0.00</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Statement Table -->
    <div class="rounded-lg shadow-default card-white">
        <div class="border-b border-stroke px-7.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">
                รายการเดินบัญชี
            </h3>
        </div>

        <div id="loading" class="p-7.5">
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-solid border-primary border-r-transparent"></div>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">กำลังโหลดข้อมูล...</p>
                </div>
            </div>
        </div>

        <div id="no-data" class="hidden p-7.5">
            <div class="py-12 text-center text-gray-600 dark:text-gray-400">
                <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-4 text-lg font-medium">ไม่มีข้อมูล</p>
                <p class="mt-2">กรุณาเลือกบัญชีและกดแสดงข้อมูล</p>
            </div>
        </div>

        <div id="statement-table-wrapper" class="hidden p-7.5">
            <div class="overflow-x-auto">
                <table id="statement-table" class="w-full display stripe hover">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">วันที่</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">ประเภท</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">เลขที่เช็ค</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">เงินฝาก</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">เงินถอน</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">คงเหลือ</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">เอกสารอ้างอิง</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">remark</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-gray-100">หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody id="statement-tbody" class="text-gray-700 dark:text-gray-300">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
@endpush

<script>
const bankStatement = {
    table: null,

    async loadStatement() {
        const account = document.getElementById('account-select').value;
        const year = document.getElementById('year-select').value;
        const month = document.getElementById('month-select').value;

        if (!account) {
            alert('กรุณาเลือกบัญชี');
            return;
        }

        // Show loading
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('no-data').classList.add('hidden');
        document.getElementById('statement-table-wrapper').classList.add('hidden');
        document.getElementById('account-info').classList.add('hidden');

        try {
            const response = await fetch(`{{ route("financial.statement.data") }}?account=${account}&year=${year}&month=${month}`);
            const data = await response.json();

            document.getElementById('loading').classList.add('hidden');

            if (!data.success) {
                throw new Error(data.message);
            }

            if (!data.statements || data.statements.length === 0) {
                document.getElementById('no-data').classList.remove('hidden');
                return;
            }

            // Show account info
            document.getElementById('account-info').classList.remove('hidden');
            document.getElementById('account-code').textContent = data.account.BNKAC_CODE;
            document.getElementById('account-name').textContent = data.account.BNKAC_NAME;
            document.getElementById('period-text').textContent = `${this.getThaiMonth(data.month)} ${parseInt(data.year) + 543}`;
            document.getElementById('trans-count').textContent = data.count;

            // Show summary
            if (data.summary) {
                document.getElementById('carry-over').textContent = this.formatNumber(data.summary.carry_over);
                document.getElementById('total-debit').textContent = this.formatNumber(data.summary.total_debit);
                document.getElementById('total-credit').textContent = this.formatNumber(data.summary.total_credit);
                document.getElementById('ending-balance').textContent = this.formatNumber(data.summary.ending_balance);
            }

            // Destroy existing table
            if (this.table) {
                this.table.destroy();
            }

            // Render table
            const tbody = document.getElementById('statement-tbody');
            tbody.innerHTML = '';

            data.statements.forEach(row => {
                const isCarryOver = row.BSTM_KEY === 0;
                const tr = document.createElement('tr');
                tr.className = isCarryOver
                    ? 'bg-blue-50 dark:bg-blue-900 font-semibold'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-800';

                tr.innerHTML = `
                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">${this.formatDate(row.BSTM_RECNL_DD)}</td>
                    <td class="px-4 py-3">
                        ${row.Statusx ? `<span class="inline-flex rounded-full px-3 py-1 text-xs font-medium ${this.getStatusColor(row.Statusx)}" title="BSTM_TYPE: ${row.BSTM_TYPE || 'N/A'}" style="cursor: help;">
                            ${row.Statusx}
                        </span>` : ''}
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${row.BSTM_CHEQUE_NO || '-'}</td>
                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400 font-medium">
                        ${row.BSTM_DEBIT !== null ? this.formatNumber(row.BSTM_DEBIT) : ''}
                    </td>
                    <td class="px-4 py-3 text-right text-red-600 dark:text-red-400 font-medium">
                        ${row.BSTM_CREDIT !== null ? this.formatNumber(row.BSTM_CREDIT) : ''}
                    </td>
                    <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400 font-bold">
                        ${this.formatNumber(row.Balance)}
                    </td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${row.DocREf || ''}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${row.DI_REMARK || ''}</td>
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${row.BSTM_REMARK || ''}</td>
                `;
                tbody.appendChild(tr);
            });

            // Show table
            document.getElementById('statement-table-wrapper').classList.remove('hidden');

            // Initialize DataTable
            this.table = $('#statement-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                },
                order: [], // ไม่เรียงใหม่ ให้ตามลำดับที่มาจาก query
                pageLength: 25,
                responsive: true,
                searching: true,
                paging: true,
                info: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]]
            });

        } catch (error) {
            document.getElementById('loading').classList.add('hidden');
            console.error('Failed to load statement:', error);
            alert('ไม่สามารถโหลดข้อมูลได้: ' + error.message);
        }
    },

    getStatusColor(status) {
        const colors = {
            'ยอดยกมา': 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            'ฝาก': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'รับชำระหนี้': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'โอนระหว่างบัญชี': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            'เช็คผ่าน': 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            'จ่ายชำระหนี้': 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            'ค่าธรรมเนียม': 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300'
        };
        return colors[status] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
    },

    formatNumber(num) {
        if (num === null || num === undefined) return '';
        return parseFloat(num).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    getThaiMonth(month) {
        const months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                       'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        return months[parseInt(month) - 1] || '';
    }
};

// Auto-load if account is specified
document.addEventListener('DOMContentLoaded', () => {
    @if($accountKey ?? false)
        bankStatement.loadStatement();
    @else
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('no-data').classList.remove('hidden');
    @endif
});
</script>
@endsection
