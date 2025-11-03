@extends('tailadmin.layouts.app')

@section('title', 'Cheque Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    /* DataTables Dark Mode - เพิ่มความเข้ม */
    .dark .dataTables_wrapper {
        color: #DEE4EE;
        background-color: transparent;
    }

    .dark .dataTables_wrapper .dataTables_length,
    .dark .dataTables_wrapper .dataTables_filter,
    .dark .dataTables_wrapper .dataTables_info,
    .dark .dataTables_wrapper .dataTables_paginate {
        color: #DEE4EE;
    }

    .dark .dataTables_wrapper .dataTables_length select,
    .dark .dataTables_wrapper .dataTables_filter input {
        background-color: #24303F;
        color: #DEE4EE;
        border: 1px solid #3E4954;
        padding: 0.5rem;
        border-radius: 0.375rem;
    }

    .dark .dataTables_wrapper .dataTables_length select:focus,
    .dark .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #3C50E0;
        outline: none;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #8A99AF !important;
        background: transparent;
        border: none;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #DEE4EE !important;
        background: #313D4A;
        border: none;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #fff !important;
        background: #3C50E0 !important;
        border: none;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: #64748B !important;
        background: transparent;
    }

    /* Table rows dark mode - เพิ่มความเข้ม */
    .dark table tbody tr {
        background-color: #24303F;
    }

    .dark table tbody tr:nth-child(even) {
        background-color: #1A222C;
    }

    .dark table tbody tr:hover {
        background-color: #313D4A !important;
    }

    /* Period buttons - เรียบหรูทางการ */
    .period-btn-all {
        background: #64748B;
        color: white !important;
        border: 2px solid #64748B;
    }

    .period-btn-all:hover {
        background: #475569;
        border-color: #475569;
    }

    .dark .period-btn-all {
        background: #334155;
        border-color: #475569;
    }

    .dark .period-btn-all:hover {
        background: #475569;
        border-color: #64748B;
    }

    .period-btn-overdue {
        background: #DC2626;
        color: white !important;
        border: 2px solid #DC2626;
    }

    .period-btn-overdue:hover {
        background: #B91C1C;
        border-color: #B91C1C;
    }

    .dark .period-btn-overdue {
        background: #7F1D1D;
        border-color: #991B1B;
    }

    .dark .period-btn-overdue:hover {
        background: #991B1B;
        border-color: #DC2626;
    }

    .period-btn-7days {
        background: #EA580C;
        color: white !important;
        border: 2px solid #EA580C;
    }

    .period-btn-7days:hover {
        background: #C2410C;
        border-color: #C2410C;
    }

    .dark .period-btn-7days {
        background: #7C2D12;
        border-color: #9A3412;
    }

    .dark .period-btn-7days:hover {
        background: #9A3412;
        border-color: #EA580C;
    }

    .period-btn-14days {
        background: #CA8A04;
        color: white !important;
        border: 2px solid #CA8A04;
    }

    .period-btn-14days:hover {
        background: #A16207;
        border-color: #A16207;
    }

    .dark .period-btn-14days {
        background: #713F12;
        border-color: #854D0E;
    }

    .dark .period-btn-14days:hover {
        background: #854D0E;
        border-color: #CA8A04;
    }

    .period-btn-30days {
        background: #16A34A;
        color: white !important;
        border: 2px solid #16A34A;
    }

    .period-btn-30days:hover {
        background: #15803D;
        border-color: #15803D;
    }

    .dark .period-btn-30days {
        background: #14532D;
        border-color: #166534;
    }

    .dark .period-btn-30days:hover {
        background: #166534;
        border-color: #16A34A;
    }

    /* Active state - เรียบหรู */
    .period-btn.active {
        transform: scale(1.02);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        border-width: 2px;
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

    /* Card borders ชัดเจนขึ้น */
    .card-filter {
        border: 2px solid #E5E7EB;
        background-color: #FFFFFF;
    }

    .dark .card-filter {
        border: 2px solid #374151;
        background-color: #1A222C;
    }

    .card-summary {
        border: 2px solid #E5E7EB;
        background-color: #F9FAFB;
    }

    .dark .card-summary {
        border: 2px solid #374151;
        background-color: #1F2937;
    }
</style>
@endpush

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            Cheque Management
        </h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-primary" href="{{ route('financial.dashboard') }}">Financial Dashboard /</a>
                </li>
                <li class="font-medium">Cheques</li>
            </ol>
        </nav>
    </div>

    <!-- Company Info -->
    <div class="mb-6 rounded-lg px-7.5 py-4 shadow-default card-filter">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-black dark:text-white">
                    {{ $company->label }}
                </h3>
                <p class="text-sm text-bodydark">
                    Company Code: {{ $company->key }}
                </p>
            </div>
            <button
                onclick="chequeManager.refreshData()"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-center font-medium text-white hover:bg-opacity-90 lg:px-5 xl:px-6"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-lg px-7.5 py-6 shadow-default card-filter">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Bank Account Filter -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                    เลือกบัญชีธนาคาร
                </label>
                <select id="account-select" onchange="chequeManager.setAccount(this.value)" class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary dark:text-white">
                    <option value="">ทั้งหมด</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->BNKAC_KEY }}" {{ $accountKey == $account->BNKAC_KEY ? 'selected' : '' }}>
                            {{ $account->BNKAC_CODE }} - {{ $account->BNKAC_NAME }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cheque Status Filter -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                    สถานะเช็ค
                </label>
                <select id="status-select" onchange="chequeManager.setStatus(this.value)" class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary dark:text-white">
                    <option value="all">ทั้งหมด</option>
                    <option value="pending" selected>ยังไม่ผ่านเช็ค</option>
                    <option value="cleared">ผ่านเช็คแล้ว</option>
                </select>
            </div>
        </div>

        <!-- Period Filter Tabs -->
        <div>
            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                ช่วงเวลาครบกำหนด
            </label>
            <div class="flex flex-wrap gap-3">
                <button onclick="chequeManager.setPeriod('all')" data-period="all" class="period-btn period-btn-all rounded-lg px-5 py-3 text-sm font-medium shadow-md hover:shadow-lg transition-all">
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">ทั้งหมด</span>
                        <span id="all-summary" class="text-xs opacity-90 mt-0.5">0 ใบ • 0 บาท</span>
                    </div>
                </button>
                <button onclick="chequeManager.setPeriod('overdue')" data-period="overdue" class="period-btn period-btn-overdue rounded-lg px-5 py-3 text-sm font-medium shadow-md hover:shadow-lg transition-all">
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">เกินกำหนด</span>
                        <span id="overdue-summary" class="text-xs opacity-90 mt-0.5">0 ใบ • 0 บาท</span>
                    </div>
                </button>
                <button onclick="chequeManager.setPeriod('7days')" data-period="7days" class="period-btn period-btn-7days rounded-lg px-5 py-3 text-sm font-medium shadow-md hover:shadow-lg transition-all">
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">ภายใน 7 วัน</span>
                        <span id="7days-summary" class="text-xs opacity-90 mt-0.5">0 ใบ • 0 บาท</span>
                    </div>
                </button>
                <button onclick="chequeManager.setPeriod('14days')" data-period="14days" class="period-btn period-btn-14days rounded-lg px-5 py-3 text-sm font-medium shadow-md hover:shadow-lg transition-all">
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">8-14 วัน</span>
                        <span id="14days-summary" class="text-xs opacity-90 mt-0.5">0 ใบ • 0 บาท</span>
                    </div>
                </button>
                <button onclick="chequeManager.setPeriod('30days')" data-period="30days" class="period-btn period-btn-30days rounded-lg px-5 py-3 text-sm font-medium shadow-md hover:shadow-lg transition-all">
                    <div class="flex flex-col items-start">
                        <span class="font-bold text-base">15-30 วัน</span>
                        <span id="30days-summary" class="text-xs opacity-90 mt-0.5">0 ใบ • 0 บาท</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="mb-6 rounded-lg px-7.5 py-4 shadow-default card-summary">
        <div id="summary-loading" class="animate-pulse">
            <div class="h-6 bg-gray-200 rounded dark:bg-gray-700 mb-2 w-1/3"></div>
            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-1/4"></div>
        </div>
        <div id="summary-content" class="hidden">
            <h4 class="text-xl font-semibold text-black dark:text-white mb-2">
                <span id="total-cheques">0</span> เช็ค
            </h4>
            <p class="text-sm text-bodydark">
                ยอดรวม: <span id="total-amount" class="font-semibold">0.00</span> บาท
            </p>
        </div>
    </div>

    <!-- Cheques Table -->
    <div class="rounded-lg px-7.5 py-6 shadow-default card-filter">
        <div class="overflow-x-auto">
            <table id="cheques-table" class="display w-full">
                <thead>
                    <tr>
                        <th>เลขที่อ้างอิง</th>
                        <th>บัญชี</th>
                        <th>เลขที่เช็ค</th>
                        <th>วันที่ครบกำหนด</th>
                        <th>จำนวนเงิน</th>
                        <th>คงเหลือ</th>
                        <th>ผู้รับเงิน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody id="cheques-tbody">
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-3 text-primary" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                กำลังโหลดข้อมูล...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
const chequeManager = {
    currentPeriod: '{{ $period }}',
    currentAccount: '{{ $accountKey ?? '' }}',
    currentStatus: 'pending',
    dataTable: null,

    init() {
        this.updateActiveButton();
        this.loadData();
    },

    setPeriod(period) {
        this.currentPeriod = period;
        this.updateActiveButton();
        this.loadData();

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('period', period);
        if (this.currentAccount) {
            url.searchParams.set('account', this.currentAccount);
        }
        if (this.currentStatus !== 'all') {
            url.searchParams.set('status', this.currentStatus);
        }
        window.history.pushState({}, '', url);
    },

    setAccount(accountKey) {
        this.currentAccount = accountKey;
        this.loadData();

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('period', this.currentPeriod);
        if (accountKey) {
            url.searchParams.set('account', accountKey);
        } else {
            url.searchParams.delete('account');
        }
        if (this.currentStatus !== 'all') {
            url.searchParams.set('status', this.currentStatus);
        }
        window.history.pushState({}, '', url);
    },

    setStatus(status) {
        this.currentStatus = status;
        this.loadData();

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('period', this.currentPeriod);
        if (this.currentAccount) {
            url.searchParams.set('account', this.currentAccount);
        }
        if (status !== 'all') {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.history.pushState({}, '', url);
    },

    updateActiveButton() {
        document.querySelectorAll('.period-btn').forEach(btn => {
            const btnPeriod = btn.getAttribute('data-period');
            if (btnPeriod === this.currentPeriod) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    },

    async loadData() {
        try {
            let url = `{{ route('financial.cheques.data') }}?period=${this.currentPeriod}`;
            if (this.currentAccount) {
                url += `&account=${this.currentAccount}`;
            }
            if (this.currentStatus && this.currentStatus !== 'all') {
                url += `&status=${this.currentStatus}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Failed to load data');
            }

            this.updateSummary(data.summary);
            this.updatePeriodButtons(data.period_summary);
            this.updateTable(data.cheques);

        } catch (error) {
            console.error('Failed to load cheque data:', error);
            alert('ไม่สามารถโหลดข้อมูลเช็คได้: ' + error.message);
        }
    },

    updateSummary(summary) {
        document.getElementById('summary-loading').classList.add('hidden');
        document.getElementById('summary-content').classList.remove('hidden');

        document.getElementById('total-cheques').textContent = summary.total_cheques.toLocaleString();
        document.getElementById('total-amount').textContent = parseFloat(summary.total_amount).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    updatePeriodButtons(periodSummary) {
        // Update each period button with count and amount
        Object.keys(periodSummary).forEach(period => {
            const summary = periodSummary[period];
            const element = document.getElementById(`${period}-summary`);

            if (element) {
                const count = summary.count || 0;
                const amount = summary.amount || 0;
                const amountText = this.formatAmount(amount);

                element.textContent = `${count} ใบ • ${amountText} บาท`;
            }
        });
    },

    formatAmount(amount) {
        if (amount >= 1000000) {
            return (amount / 1000000).toFixed(1) + 'M';
        } else if (amount >= 1000) {
            return (amount / 1000).toFixed(0) + 'K';
        }
        return amount.toLocaleString('th-TH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    },

    updateTable(cheques) {
        // Destroy existing DataTable properly
        if (this.dataTable) {
            this.dataTable.destroy();
            this.dataTable = null;
        }

        const tbody = document.getElementById('cheques-tbody');
        tbody.innerHTML = '';

        if (cheques.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-bodydark dark:text-bodydark">ไม่พบข้อมูลเช็ค</td></tr>';
            return;
        }

        cheques.forEach(cheque => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-2 dark:hover:bg-meta-4';

            // Determine badge color based on period - ปรับให้ชัดเจนใน dark mode
            let badgeClass = 'bg-gray-2 text-body dark:bg-meta-4 dark:text-bodydark';
            if (cheque.PeriodBucket === 'เกินกำหนด') {
                badgeClass = 'bg-red-light text-danger dark:bg-danger dark:bg-opacity-10 dark:text-danger';
            } else if (cheque.PeriodBucket === 'ภายใน 7 วัน') {
                badgeClass = 'bg-warning bg-opacity-10 text-warning dark:bg-warning dark:bg-opacity-10 dark:text-warning';
            } else if (cheque.PeriodBucket === '8–14 วัน') {
                badgeClass = 'bg-warning bg-opacity-5 text-warning dark:bg-warning dark:bg-opacity-5 dark:text-warning';
            } else if (cheque.PeriodBucket === '15–30 วัน') {
                badgeClass = 'bg-success bg-opacity-10 text-success dark:bg-success dark:bg-opacity-10 dark:text-success';
            }

            row.innerHTML = `
                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                    <p class="text-black dark:text-white">${cheque.ReferenceNo || '-'}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                    <p class="text-black dark:text-white">${cheque.BankAccountKey || '-'}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                    <p class="text-black dark:text-white">${cheque.ChequeNo || '-'}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                    <p class="text-black dark:text-white">${this.formatDate(cheque.ChequeDate)}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 text-right dark:border-strokedark">
                    <p class="text-black dark:text-white">${this.formatNumber(cheque.Amount)}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 text-right dark:border-strokedark">
                    <p class="text-black dark:text-white">${this.formatNumber(cheque.OutstandingAmount)}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                    <p class="text-black dark:text-white">${cheque.Payee || '-'}</p>
                </td>
                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium ${badgeClass}">
                        ${cheque.PeriodBucket}
                    </span>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Initialize DataTable
        this.dataTable = $('#cheques-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
            },
            order: [[3, 'asc']], // Sort by date (now column 3)
            pageLength: 25,
            responsive: true,
            destroy: true // Allow re-initialization
        });
    },

    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    formatNumber(value) {
        if (!value) return '0.00';
        return parseFloat(value).toLocaleString('th-TH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    refreshData() {
        this.loadData();
    }
};

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    chequeManager.init();
});
</script>
@endpush
