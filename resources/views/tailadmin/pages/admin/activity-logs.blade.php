@extends('tailadmin.layouts.app')

@section('title', 'ประวัติการใช้งาน')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">
            ประวัติการใช้งาน
        </h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-primary">ประวัติการใช้งาน</li>
            </ol>
        </nav>
    </div>

    <!-- Filters Card -->
    <div class="mb-6 rounded-lg border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="border-b border-stroke px-7.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">ตัวกรอง</h3>
        </div>
        <div class="p-7.5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Date From -->
                <div>
                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                        จากวันที่
                    </label>
                    <input type="date" id="dateFrom"
                        class="w-full rounded border border-stroke bg-transparent px-4 py-2.5 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                </div>

                <!-- Date To -->
                <div>
                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                        ถึงวันที่
                    </label>
                    <input type="date" id="dateTo"
                        class="w-full rounded border border-stroke bg-transparent px-4 py-2.5 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                </div>

                <!-- User Filter -->
                <div>
                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                        ผู้ใช้งาน
                    </label>
                    <select id="userFilter"
                        class="w-full rounded border border-stroke bg-transparent px-4 py-2.5 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        <option value="">ทั้งหมด</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Method Filter -->
                <div>
                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                        HTTP Method
                    </label>
                    <select id="methodFilter"
                        class="w-full rounded border border-stroke bg-transparent px-4 py-2.5 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                        <option value="">ทั้งหมด</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
            </div>

            <!-- Search Box -->
            <div class="mt-4">
                <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                    ค้นหา (URL, IP, Action)
                </label>
                <div class="flex gap-2">
                    <input type="text" id="searchBox" placeholder="ค้นหา..."
                        class="flex-1 rounded border border-stroke bg-transparent px-4 py-2.5 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" />
                    <button onclick="activityLogs.applyFilters()"
                        class="rounded bg-primary px-6 py-2.5 text-white hover:bg-opacity-90">
                        ค้นหา
                    </button>
                    <button onclick="activityLogs.resetFilters()"
                        class="rounded border border-stroke px-6 py-2.5 hover:bg-gray-50 dark:border-strokedark dark:hover:bg-meta-4">
                        รีเซ็ต
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="rounded-lg border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="flex items-center justify-between border-b border-stroke px-7.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">
                ผลการค้นหา (<span id="totalCount">0</span> รายการ)
            </h3>
            <div class="flex items-center gap-2">
                <label class="text-sm text-black dark:text-white">แสดง:</label>
                <select id="perPage" onchange="activityLogs.changePerPage()"
                    class="rounded border border-stroke bg-transparent px-3 py-1.5 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input">
                    <option value="25">25</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <!-- Loading Skeleton -->
            <div id="loadingSkeleton" class="p-7.5" style="display: none;">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                </div>
            </div>

            <!-- Table -->
            <table id="logsTable" class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-4 py-4 font-medium text-black dark:text-white w-20">ID</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">ผู้ใช้งาน</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Action</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white w-24">Method</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white w-32">IP</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white w-40">วันที่/เวลา</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <!-- Populated by JavaScript -->
                </tbody>
            </table>

            <!-- Empty State -->
            <div id="emptyState" class="py-12 text-center" style="display: none;">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">ไม่พบข้อมูล</p>
            </div>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="border-t border-stroke px-7.5 py-4 dark:border-strokedark" style="display: none;">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    แสดง <span id="showingFrom">0</span> ถึง <span id="showingTo">0</span> จาก <span id="showingTotal">0</span> รายการ
                </div>
                <div class="flex gap-2" id="paginationButtons">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-lg bg-white dark:bg-boxdark">
        <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-black dark:text-white">รายละเอียด Activity Log</h3>
                <button onclick="activityLogs.closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4" id="modalContent">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const activityLogs = {
    currentPage: 1,

    async loadData(page = 1) {
        this.currentPage = page;

        // Show loading
        document.getElementById('loadingSkeleton').style.display = 'block';
        document.getElementById('logsTable').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('paginationContainer').style.display = 'none';

        try {
            const params = new URLSearchParams({
                page: page,
                per_page: document.getElementById('perPage').value,
                user_id: document.getElementById('userFilter').value,
                method: document.getElementById('methodFilter').value,
                date_from: document.getElementById('dateFrom').value,
                date_to: document.getElementById('dateTo').value,
                search: document.getElementById('searchBox').value
            });

            const response = await fetch('{{ route('admin.log.data') }}?' + params, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) throw new Error('Failed to load data');

            const data = await response.json();

            this.renderTable(data);
            this.renderPagination(data);

        } catch (error) {
            console.error('Error loading logs:', error);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถโหลดข้อมูลได้'
            });
        } finally {
            document.getElementById('loadingSkeleton').style.display = 'none';
        }
    },

    renderTable(data) {
        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = '';

        document.getElementById('totalCount').textContent = data.total;

        if (data.data.length === 0) {
            document.getElementById('logsTable').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
            return;
        }

        document.getElementById('logsTable').style.display = 'table';
        document.getElementById('emptyState').style.display = 'none';

        data.data.forEach(log => {
            const row = document.createElement('tr');
            row.className = 'border-b border-stroke dark:border-strokedark hover:bg-gray-50 dark:hover:bg-meta-4 cursor-pointer';
            row.onclick = () => this.showDetail(log.id);

            const methodBadge = this.getMethodBadge(log.method);
            const userName = log.user ? `${log.user.name}<br><small class="text-xs text-gray-500">${log.user.email}</small>` : '<span class="text-gray-400">System</span>';
            const actionDisplay = log.action || `${log.method} ${log.url}`;
            const formattedDate = new Date(log.created_at).toLocaleString('th-TH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            row.innerHTML = `
                <td class="px-4 py-3 text-black dark:text-white">${log.id}</td>
                <td class="px-4 py-3 text-black dark:text-white">${userName}</td>
                <td class="px-4 py-3 text-black dark:text-white"><div class="max-w-md truncate">${actionDisplay}</div></td>
                <td class="px-4 py-3">${methodBadge}</td>
                <td class="px-4 py-3 text-black dark:text-white">${log.ip || '-'}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${formattedDate}</td>
            `;

            tbody.appendChild(row);
        });
    },

    getMethodBadge(method) {
        const colors = {
            'POST': 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
            'PUT': 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400',
            'PATCH': 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
            'DELETE': 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400'
        };

        const colorClass = colors[method] || 'bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400';

        return `<span class="inline-flex rounded-full px-3 py-1 text-sm font-medium ${colorClass}">${method}</span>`;
    },

    renderPagination(data) {
        if (data.last_page <= 1) {
            document.getElementById('paginationContainer').style.display = 'none';
            return;
        }

        document.getElementById('paginationContainer').style.display = 'block';
        document.getElementById('showingFrom').textContent = data.from || 0;
        document.getElementById('showingTo').textContent = data.to || 0;
        document.getElementById('showingTotal').textContent = data.total;

        const buttonsContainer = document.getElementById('paginationButtons');
        buttonsContainer.innerHTML = '';

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '&laquo; ก่อนหน้า';
        prevBtn.className = `px-4 py-2 rounded border ${data.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-meta-4'} border-stroke dark:border-strokedark`;
        prevBtn.disabled = data.current_page === 1;
        prevBtn.onclick = () => this.loadData(data.current_page - 1);
        buttonsContainer.appendChild(prevBtn);

        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = `px-4 py-2 rounded border ${i === data.current_page ? 'bg-primary text-white' : 'hover:bg-gray-50 dark:hover:bg-meta-4'} border-stroke dark:border-strokedark`;
                pageBtn.onclick = () => this.loadData(i);
                buttonsContainer.appendChild(pageBtn);
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.className = 'px-2 py-2';
                buttonsContainer.appendChild(dots);
            }
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = 'ถัดไป &raquo;';
        nextBtn.className = `px-4 py-2 rounded border ${data.current_page === data.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-meta-4'} border-stroke dark:border-strokedark`;
        nextBtn.disabled = data.current_page === data.last_page;
        nextBtn.onclick = () => this.loadData(data.current_page + 1);
        buttonsContainer.appendChild(nextBtn);
    },

    async showDetail(id) {
        try {
            const response = await fetch(`{{ route('admin.log.show', ':id') }}`.replace(':id', id), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) throw new Error('Failed to load detail');

            const log = await response.json();

            const content = document.getElementById('modalContent');
            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">ผู้ใช้งาน</h4>
                        <p class="mt-1 text-black dark:text-white">${log.user ? `${log.user.name} (${log.user.email})` : 'System'}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Method</h4>
                        <p class="mt-1">${this.getMethodBadge(log.method)}</p>
                    </div>
                    <div class="col-span-2">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Action/Route</h4>
                        <p class="mt-1 text-black dark:text-white">${log.action || '-'}</p>
                    </div>
                    <div class="col-span-2">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">URL</h4>
                        <p class="mt-1 break-all text-black dark:text-white">${log.url}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">IP Address</h4>
                        <p class="mt-1 text-black dark:text-white">${log.ip || '-'}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">วันที่/เวลา</h4>
                        <p class="mt-1 text-black dark:text-white">${new Date(log.created_at).toLocaleString('th-TH')}</p>
                    </div>
                    ${log.user_agent ? `
                    <div class="col-span-2">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">User Agent</h4>
                        <p class="mt-1 text-sm break-all text-gray-600 dark:text-gray-400">${log.user_agent}</p>
                    </div>
                    ` : ''}
                    ${log.payload ? `
                    <div class="col-span-2">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Payload</h4>
                        <pre class="rounded bg-gray-100 dark:bg-gray-800 p-4 text-xs overflow-auto max-h-64"><code>${JSON.stringify(log.payload, null, 2)}</code></pre>
                    </div>
                    ` : ''}
                </div>
            `;

            document.getElementById('detailModal').classList.remove('hidden');
            document.getElementById('detailModal').classList.add('flex');

        } catch (error) {
            console.error('Error loading detail:', error);
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถโหลดรายละเอียดได้'
            });
        }
    },

    closeModal() {
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModal').classList.remove('flex');
    },

    applyFilters() {
        this.loadData(1);
    },

    resetFilters() {
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('userFilter').value = '';
        document.getElementById('methodFilter').value = '';
        document.getElementById('searchBox').value = '';
        this.loadData(1);
    },

    changePerPage() {
        this.loadData(1);
    }
};

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    activityLogs.loadData();
});

// Enter key to search
document.getElementById('searchBox').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        activityLogs.applyFilters();
    }
});
</script>
@endpush

@endsection
