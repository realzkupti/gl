@extends('tailadmin.layouts.app')

@section('title', 'รายงานเช็ค - ระบบเช็ค')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">รายงานเช็ค</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">Dashboard /</a></li>
                <li><a href="{{ route('cheque.print') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">รายงาน</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <!-- Filters & Summary Stats -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">ค้นหาและกรองข้อมูล</h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium">วันที่เริ่มต้น</label>
                    <input type="date" id="start-date" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">วันที่สิ้นสุด</label>
                    <input type="date" id="end-date" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">สาขา</label>
                    <select id="filter-branch" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                        <option value="">ทุกสาขา</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">ธนาคาร</label>
                    <select id="filter-bank" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                        <option value="">ทั้งหมด</option>
                        <option value="กสิกรไทย">กสิกรไทย</option>
                        <option value="ไทยพาณิชย์">ไทยพาณิชย์</option>
                        <option value="กรุงเทพ">กรุงเทพ</option>
                        <option value="กรุงไทย">กรุงไทย</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex gap-3">
                <button onclick="searchCheques()" class="rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                    ค้นหา
                </button>
                <button onclick="resetFilters()" class="rounded border border-gray-300 px-6 py-2.5 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                    รีเซ็ต
                </button>
                <button onclick="exportExcel()" class="ml-auto rounded bg-green-500 px-6 py-2.5 text-white hover:bg-green-600">
                    Export Excel
                </button>
                <button onclick="exportPDF()" class="rounded bg-red-500 px-6 py-2.5 text-white hover:bg-red-600">
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">จำนวนเช็คทั้งหมด</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white" id="total-count">0</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">มูลค่ารวม</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white" id="total-amount">฿0.00</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">มูลค่าเฉลี่ย</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white" id="avg-amount">฿0.00</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">เช็คสูงสุด</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white" id="max-amount">฿0.00</div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">รายการเช็ค</h3>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">วันที่</th>
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">เลขที่</th>
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">สาขา</th>
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">ธนาคาร</th>
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">ผู้รับเงิน</th>
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white text-right">จำนวนเงิน</th>
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cheque-results">
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    กรุณาเลือกเงื่อนไขการค้นหา
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-400" id="results-info">
                        แสดง 0 รายการ
                    </p>
                    <div id="pagination" class="flex gap-2">
                        <!-- Pagination buttons will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let totalPages = 1;
let allCheques = [];

// Load branches
async function loadBranches() {
    try {
        const response = await axios.get('/api/branches');
        const select = document.getElementById('filter-branch');
        response.data.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id;
            option.textContent = `${branch.code} - ${branch.name}`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Failed to load branches:', error);
    }
}

// Search cheques
async function searchCheques() {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    const branchId = document.getElementById('filter-branch').value;
    const bank = document.getElementById('filter-bank').value;

    try {
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (branchId) params.append('branch_id', branchId);
        if (bank) params.append('bank', bank);

        const response = await axios.get('/api/cheques?' + params.toString());
        allCheques = response.data;

        updateSummary();
        displayResults();
    } catch (error) {
        console.error('Search failed:', error);
        alert('เกิดข้อผิดพลาดในการค้นหา');
    }
}

// Update summary cards
function updateSummary() {
    const count = allCheques.length;
    const total = allCheques.reduce((sum, c) => sum + parseFloat(c.amount || 0), 0);
    const avg = count > 0 ? total / count : 0;
    const max = count > 0 ? Math.max(...allCheques.map(c => parseFloat(c.amount || 0))) : 0;

    document.getElementById('total-count').textContent = count.toLocaleString();
    document.getElementById('total-amount').textContent = '฿' + total.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('avg-amount').textContent = '฿' + avg.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('max-amount').textContent = '฿' + max.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Display results in table
function displayResults() {
    const tbody = document.getElementById('cheque-results');
    const perPage = 10;
    totalPages = Math.ceil(allCheques.length / perPage);
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const pageData = allCheques.slice(start, end);

    if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">ไม่พบข้อมูล</td></tr>';
        document.getElementById('results-info').textContent = 'แสดง 0 รายการ';
        return;
    }

    tbody.innerHTML = pageData.map(cheque => `
        <tr class="border-b border-gray-200 dark:border-gray-800">
            <td class="px-4 py-4 text-gray-900 dark:text-white">${cheque.date}</td>
            <td class="px-4 py-4 text-gray-900 dark:text-white">${cheque.number}</td>
            <td class="px-4 py-4 text-gray-600 dark:text-gray-400">${cheque.branch?.name || '-'}</td>
            <td class="px-4 py-4 text-gray-600 dark:text-gray-400">${cheque.bank}</td>
            <td class="px-4 py-4 text-gray-600 dark:text-gray-400">${cheque.payee}</td>
            <td class="px-4 py-4 text-gray-900 dark:text-white text-right font-semibold">฿${parseFloat(cheque.amount).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
            <td class="px-4 py-4 text-center">
                <button onclick="viewCheque(${cheque.id})" class="text-blue-500 hover:text-blue-700" title="ดูรายละเอียด">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
                <button onclick="printCheque(${cheque.id})" class="ml-2 text-green-500 hover:text-green-700" title="พิมพ์">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </button>
            </td>
        </tr>
    `).join('');

    document.getElementById('results-info').textContent = `แสดง ${start + 1}-${Math.min(end, allCheques.length)} จาก ${allCheques.length} รายการ`;
    renderPagination();
}

// Render pagination
function renderPagination() {
    const pagination = document.getElementById('pagination');
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '';
    html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="rounded border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 disabled:opacity-50">Previous</button>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<button class="rounded bg-brand-500 px-4 py-2 text-white">${i}</button>`;
        } else if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button onclick="changePage(${i})" class="rounded border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<span class="px-2">...</span>`;
        }
    }

    html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="rounded border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 disabled:opacity-50">Next</button>`;

    pagination.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    displayResults();
}

// Reset filters
function resetFilters() {
    document.getElementById('start-date').value = '';
    document.getElementById('end-date').value = '';
    document.getElementById('filter-branch').value = '';
    document.getElementById('filter-bank').value = '';
    allCheques = [];
    document.getElementById('cheque-results').innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">กรุณาเลือกเงื่อนไขการค้นหา</td></tr>';
    document.getElementById('total-count').textContent = '0';
    document.getElementById('total-amount').textContent = '฿0.00';
    document.getElementById('avg-amount').textContent = '฿0.00';
    document.getElementById('max-amount').textContent = '฿0.00';
    document.getElementById('results-info').textContent = 'แสดง 0 รายการ';
    document.getElementById('pagination').innerHTML = '';
}

// View cheque detail
function viewCheque(id) {
    const cheque = allCheques.find(c => c.id === id);
    if (!cheque) return;

    alert(`รายละเอียดเช็ค\n\nเลขที่: ${cheque.number}\nวันที่: ${cheque.date}\nธนาคาร: ${cheque.bank}\nผู้รับเงิน: ${cheque.payee}\nจำนวนเงิน: ฿${parseFloat(cheque.amount).toLocaleString('th-TH', {minimumFractionDigits: 2})}`);
}

// Print cheque
function printCheque(id) {
    window.open(`/cheque/print?id=${id}`, '_blank');
}

// Export to Excel
function exportExcel() {
    if (allCheques.length === 0) {
        alert('ไม่มีข้อมูลสำหรับ Export');
        return;
    }
    alert('ฟีเจอร์ Export Excel จะพร้อมใช้งานเร็วๆ นี้');
}

// Export to PDF
function exportPDF() {
    if (allCheques.length === 0) {
        alert('ไม่มีข้อมูลสำหรับ Export');
        return;
    }
    alert('ฟีเจอร์ Export PDF จะพร้อมใช้งานเร็วๆ นี้');
}

// Set default dates (last 30 days)
function setDefaultDates() {
    const today = new Date();
    const lastMonth = new Date(today);
    lastMonth.setDate(lastMonth.getDate() - 30);

    document.getElementById('end-date').value = today.toISOString().split('T')[0];
    document.getElementById('start-date').value = lastMonth.toISOString().split('T')[0];
}

// Initialize
window.addEventListener('DOMContentLoaded', () => {
    loadBranches();
    setDefaultDates();
});
</script>
@endpush
