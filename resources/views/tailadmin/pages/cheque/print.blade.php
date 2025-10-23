@extends('tailadmin.layouts.app')

@section('title', 'พิมพ์เช็ค - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            พิมพ์เช็ค
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('cheque.print') }}">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">พิมพ์เช็ค</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: Form -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">ข้อมูลเช็ค</h3>

                <form id="cheque-form" class="space-y-4">
                    <!-- Branch -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">สาขา</label>
                        <select id="branch_code" name="branch_code" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                            <option value="">-- เลือกสาขา --</option>
                        </select>
                    </div>

                    <!-- Bank -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">ธนาคาร</label>
                         <select id="bank_code" name="bank_code" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                            <option value="">-- เลือกธนาคาร --</option>
                            <option value="standard">เทมเพลตมาตรฐาน</option>
                            <option value="kbank">ธนาคารกสิกรไทย</option>
                            <option value="scb">ธนาคารไทยพาณิชย์</option>
                            <option value="bbl">ธนาคารกรุงเทพ</option>
                            <option value="custom">กำหนดเอง</option>
                        </select>
                        {{-- <input type="text" id="bank" name="bank" placeholder="ธนาคาร" required
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" /> --}}
                    </div>

                    <!-- Cheque Number -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">เลขที่เช็ค</label>
                        <div class="flex gap-2">
                            <input type="text" id="cheque_number" name="cheque_number" placeholder="เลขที่เช็ค" required
                                class="flex-1 rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                            <button type="button" onclick="getNextChequeNumber()" class="rounded bg-gray-100 px-4 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">วันที่</label>
                        <input type="date" id="date" name="date" required
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                    </div>

                    <!-- Payee -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">จ่ายให้แก่</label>
                        <input type="text" id="payee" name="payee" placeholder="ชื่อผู้รับเงิน" required
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">จำนวนเงิน</label>
                        <input type="number" id="amount" name="amount" placeholder="0.00" step="0.01" required
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                        <p id="amount-text" class="mt-1 text-sm text-gray-600 dark:text-gray-400"></p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="printCheque()" class="flex-1 rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                พิมพ์เช็ค
                            </span>
                        </button>
                        <button type="button" onclick="clearForm()" class="rounded border border-gray-300 px-6 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            ล้างข้อมูล
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Preview -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">ตัวอย่างเช็ค</h3>

                <div id="cheque-preview" class="aspect-[3/1] border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-800">
                    <!-- Cheque preview will be rendered here -->
                    <div class="relative h-full rounded-lg border-2 border-gray-400 bg-white p-6 shadow-lg">
                        <div class="absolute right-4 top-4 text-sm font-mono text-gray-600" id="preview-number">000000</div>
                        <div class="absolute right-4 top-8 text-sm text-gray-600" id="preview-date">__/__/____</div>

                        <div class="mt-12">
                            <div class="text-sm text-gray-600">จ่ายเงินให้แก่</div>
                            <div class="mt-1 border-b border-gray-400 pb-1 font-medium" id="preview-payee">___________________________________</div>
                        </div>

                        <div class="mt-6">
                            <div class="text-sm text-gray-600">เป็นเงิน</div>
                            <div class="mt-1 border-b border-gray-400 pb-1 font-medium" id="preview-amount-text">_________________________________________</div>
                        </div>

                        <div class="absolute bottom-6 right-6 text-xl font-bold" id="preview-amount">0.00</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cheques -->
    <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">เช็คล่าสุด</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">เลขที่</th>
                            <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">วันที่</th>
                            <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">ผู้รับเงิน</th>
                            <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">จำนวนเงิน</th>
                            <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">ธนาคาร</th>
                            <th class="px-4 py-3 font-medium text-gray-900 dark:text-white"></th>
                        </tr>
                    </thead>
                    <tbody id="recent-cheques">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">กำลังโหลด...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bahttext/dist/bahttext.min.js"></script>
<script>
// Set today's date
document.getElementById('date').valueAsDate = new Date();

// Load branches
async function loadBranches() {
    try {
        const res = await axios.get('/api/branches');
        const select = document.getElementById('branch_code');
        res.data.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.code;
            opt.textContent = `${b.code} - ${b.name}`;
            select.appendChild(opt);
        });
    } catch (e) {
        console.error('Failed to load branches', e);
    }
}


// Get next cheque number
async function getNextChequeNumber() {
    try {
        const res = await axios.get('/api/cheques/next');
        if (res.data.cheque_number) {
            document.getElementById('cheque_number').value = res.data.cheque_number;
            updatePreview();
        }
    } catch (e) {
        console.error('Failed to get next number', e);
    }
}

// Update preview
function updatePreview() {
    const number = document.getElementById('cheque_number').value;
    const date = document.getElementById('date').value;
    const payee = document.getElementById('payee').value;
    const amount = document.getElementById('amount').value;

    document.getElementById('preview-number').textContent = number || '000000';
    document.getElementById('preview-date').textContent = date ? new Date(date).toLocaleDateString('th-TH') : '__/__/____';
    document.getElementById('preview-payee').textContent = payee || '___________________________________';
    document.getElementById('preview-amount').textContent = amount ? parseFloat(amount).toLocaleString('th-TH', {minimumFractionDigits: 2}) : '0.00';

    if (amount && typeof BAHTTEXT !== 'undefined') {
        document.getElementById('preview-amount-text').textContent = BAHTTEXT(amount);
        document.getElementById('amount-text').textContent = BAHTTEXT(amount);
    }
}

// Print cheque
async function printCheque() {
    const form = document.getElementById('cheque-form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    try {
        const res = await axios.post('/api/cheques', data);
        alert('บันทึกและพิมพ์เช็คเรียบร้อย');
        clearForm();
        loadRecentCheques();
        // Here you would trigger actual printing
        window.print();
    } catch (e) {
        alert('เกิดข้อผิดพลาด: ' + (e.response?.data?.error || e.message));
    }
}

// Load recent cheques
async function loadRecentCheques() {
    try {
        const res = await axios.get('/api/cheques');
        const tbody = document.getElementById('recent-cheques');
        if (res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">ยังไม่มีข้อมูล</td></tr>';
            return;
        }

        tbody.innerHTML = res.data.slice(0, 10).map(c => `
            <tr class="border-b border-gray-200 dark:border-gray-800">
                <td class="px-4 py-3">${c.cheque_number}</td>
                <td class="px-4 py-3">${new Date(c.date).toLocaleDateString('th-TH')}</td>
                <td class="px-4 py-3">${c.payee}</td>
                <td class="px-4 py-3 text-right">${parseFloat(c.amount).toLocaleString('th-TH', {minimumFractionDigits: 2})}</td>
                <td class="px-4 py-3">${c.bank}</td>
                <td class="px-4 py-3">
                    <button onclick="deleteCheque(${c.id})" class="text-red-500 hover:text-red-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        console.error('Failed to load cheques', e);
    }
}

// Delete cheque
async function deleteCheque(id) {
    if (!confirm('ต้องการลบเช็คนี้?')) return;

    try {
        await axios.delete(`/api/cheques/${id}`);
        loadRecentCheques();
    } catch (e) {
        alert('เกิดข้อผิดพลาด');
    }
}

// Clear form
function clearForm() {
    document.getElementById('cheque-form').reset();
    document.getElementById('date').valueAsDate = new Date();
    updatePreview();
}

// Form listeners
document.getElementById('cheque_number').addEventListener('input', updatePreview);
document.getElementById('date').addEventListener('change', updatePreview);
document.getElementById('payee').addEventListener('input', updatePreview);
document.getElementById('amount').addEventListener('input', updatePreview);

// Initialize
loadBranches();
loadRecentCheques();
getNextChequeNumber();
</script>
@endpush
