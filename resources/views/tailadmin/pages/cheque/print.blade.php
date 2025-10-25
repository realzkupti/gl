@extends('tailadmin.layouts.app')

@section('title', 'พิมพ์เช็ค - ' . config('app.name'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
.cheque-workspace {
    background: #f5f5f5;
    border-radius: 10px;
    padding: 20px;
    min-height: 500px;
}

.cheque-preview {
    position: relative;
    width: 800px;
    height: 350px;
    background: white;
    border: 2px solid #ddd;
    margin: 20px auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.draggable {
    position: absolute;
    cursor: move;
    user-select: none;
    padding: 2px 5px;
    border: 1px dashed transparent;
    white-space: nowrap;
}

.draggable:hover {
    border-color: #2196F3;
    background: rgba(33, 150, 243, 0.05);
}

.draggable.selected {
    border-color: #2196F3;
    background: rgba(33, 150, 243, 0.1);
}

.ac-payee {
    color: #ff0000;
    font-weight: bold;
    font-size: 18px;
}

.line-holder {
    font-size: 20px;
    font-weight: bold;
}

.info-badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 8px 16px;
    border-radius: 20px;
    margin-right: 10px;
    font-size: 13px;
    margin-bottom: 10px;
}

@media print {
    body * {
        visibility: hidden;
    }
    .cheque-preview, .cheque-preview * {
        visibility: visible;
    }
    .cheque-preview {
        position: absolute;
        left: 0;
        top: 0;
        width: 800px;
        height: 350px;
        border: none;
        box-shadow: none;
    }
    .draggable {
        border: none !important;
        background: none !important;
    }
}
</style>
@endpush

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
                <li class="font-medium text-brand-500">พิมพ์เช็ค</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Left: Form (1 column) -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📝 ข้อมูลเช็ค</h3>

                <form id="cheque-form" class="space-y-4">
                    <!-- Branch -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">🏢 สาขา</label>
                        <select id="branch_code" name="branch_code" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                            <option value="">-- เลือกสาขา --</option>
                        </select>
                    </div>

                    <!-- Bank -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">📋 ธนาคาร</label>
                        <select id="bank_code" name="bank_code" onchange="loadBankTemplate()" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                            <option value="custom">กำหนดเอง</option>
                            <option value="scb">ธนาคารไทยพาณิชย์ (SCB)</option>
                            <option value="kbank">ธนาคารกสิกรไทย (KBANK)</option>
                            <option value="ktb">ธนาคารกรุงไทย (KTB)</option>
                        </select>
                    </div>

                    <!-- Cheque Number -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">🔢 หมายเลขเช็ค</label>
                        <div class="flex gap-2">
                            <input type="text" id="cheque_number" name="cheque_number" placeholder="เลขที่เช็ค" required
                                class="flex-1 rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                            <button type="button" onclick="useNextChequeNo()" class="rounded bg-gray-100 px-4 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700" title="เลขถัดไป">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">📅 วันที่</label>
                        <input type="date" id="date" name="date" required
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                    </div>

                    <!-- Payee -->
                    <div class="relative">
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">👤 จ่ายให้</label>
                        <input type="text" id="payee" name="payee" placeholder="ชื่อผู้รับเงิน" required autocomplete="off"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                        <div id="payee-autocomplete" class="absolute z-50 mt-1 w-full hidden bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">💰 จำนวนเงิน (บาท)</label>
                        <input type="text" id="amount" name="amount" placeholder="0.00" required oninput="updateAmountText()"
                            class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                        <p id="amount-text" class="mt-1 text-sm text-gray-600 dark:text-gray-400"></p>
                    </div>

                    <!-- Checkboxes -->
                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                            <input type="checkbox" id="show_ac_payee" checked onchange="toggleAcPayee()"
                                class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                            แสดง A/C PAYEE ONLY
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                            <input type="checkbox" id="show_line" checked onchange="toggleLine()"
                                class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                            แสดงเส้น (หรือผู้ถือ)
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col gap-2 pt-4">
                        <button type="button" onclick="printCheque()" class="w-full rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                            🖨️ พิมพ์เช็ค
                        </button>
                        <button type="button" onclick="clearForm()" class="w-full rounded border border-gray-300 px-6 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            🔄 เริ่มใหม่
                        </button>
                    </div>
                </form>

                <!-- Quick Links -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">🔗 ลิงก์ด่วน</p>
                    <div class="space-y-2">
                        <a href="{{ route('cheque.designer') }}" class="block text-sm text-brand-500 hover:text-brand-600">
                            🎨 ออกแบบ & ปรับแต่งตำแหน่ง
                        </a>
                        <a href="{{ route('cheque.reports') }}" class="block text-sm text-brand-500 hover:text-brand-600">
                            📊 รายงานการพิมพ์เช็ค
                        </a>
                        <a href="{{ route('cheque.branches') }}" class="block text-sm text-brand-500 hover:text-brand-600">
                            🏢 จัดการสาขา
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Preview (3 columns) -->
        <div class="lg:col-span-3">
            <div class="cheque-workspace">
                <div class="mb-4">
                    <span class="info-badge">💡 คลิกที่องค์ประกอบเพื่อแก้ไข</span>
                    <span class="info-badge">🖱️ ลากเพื่อย้ายตำแหน่ง</span>
                    <span class="info-badge">📐 ไปที่หน้าออกแบบเพื่อปรับแต่งขนาดและสี</span>
                </div>

                <div class="cheque-preview" id="chequePreview">
                    <div class="draggable ac-payee" id="acPayee" data-name="A/C PAYEE ONLY">
                        A/C PAYEE ONLY
                    </div>
                    <div class="draggable line-holder" id="lineHolder" data-name="เส้น (หรือผู้ถือ)">
                        --------
                    </div>
                    <div class="draggable" id="dateDisplay" data-name="วันที่"></div>
                    <div class="draggable" id="payeeDisplay" data-name="ชื่อผู้รับเงิน">
                        &lt;สั่งจ่าย&gt;
                    </div>
                    <div class="draggable" id="amountText" data-name="จำนวนเงิน (ตัวอักษร)"></div>
                    <div class="draggable" id="amountNumber" data-name="จำนวนเงิน (ตัวเลข)">
                        ***0.00***
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
// API Base URL
const API_BASE = '/api';

// Element selection
let selectedElement = null;
let draggedElement = null;
let offsetX = 0;
let offsetY = 0;

// Default positions
const defaultPositions = {
    acPayee: { top: 40, left: 200, fontSize: 18, color: '#ff0000', bold: true },
    lineHolder: { top: 85, left: 80, fontSize: 20, color: '#000000', bold: true },
    dateDisplay: { top: 40, right: 90, fontSize: 20, color: '#000000', bold: false },
    payeeDisplay: { top: 110, left: 80, fontSize: 18, color: '#000000', bold: false },
    amountText: { top: 165, left: 80, fontSize: 16, color: '#000000', bold: false },
    amountNumber: { top: 215, right: 90, fontSize: 18, color: '#000000', bold: true }
};

// Bank templates
const bankTemplates = {
    scb: {
        acPayee: { top: 35, left: 200, fontSize: 18, color: '#ff0000', bold: true },
        lineHolder: { top: 80, left: 75, fontSize: 20, color: '#000000', bold: true },
        dateDisplay: { top: 35, right: 85, fontSize: 20, color: '#000000', bold: false },
        payeeDisplay: { top: 105, left: 75, fontSize: 18, color: '#000000', bold: false },
        amountText: { top: 160, left: 75, fontSize: 16, color: '#000000', bold: false },
        amountNumber: { top: 210, right: 85, fontSize: 18, color: '#000000', bold: true }
    },
    kbank: {
        acPayee: { top: 45, left: 210, fontSize: 18, color: '#ff0000', bold: true },
        lineHolder: { top: 90, left: 85, fontSize: 20, color: '#000000', bold: true },
        dateDisplay: { top: 45, right: 95, fontSize: 20, color: '#000000', bold: false },
        payeeDisplay: { top: 115, left: 85, fontSize: 18, color: '#000000', bold: false },
        amountText: { top: 170, left: 85, fontSize: 16, color: '#000000', bold: false },
        amountNumber: { top: 220, right: 95, fontSize: 18, color: '#000000', bold: true }
    },
    ktb: {
        acPayee: { top: 38, left: 205, fontSize: 18, color: '#ff0000', bold: true },
        lineHolder: { top: 83, left: 78, fontSize: 20, color: '#000000', bold: true },
        dateDisplay: { top: 38, right: 88, fontSize: 20, color: '#000000', bold: false },
        payeeDisplay: { top: 108, left: 78, fontSize: 18, color: '#000000', bold: false },
        amountText: { top: 163, left: 78, fontSize: 16, color: '#000000', bold: false },
        amountNumber: { top: 213, right: 88, fontSize: 18, color: '#000000', bold: true }
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    loadPositions();
    setupDragAndDrop();
    setTodayDate();
    loadFormData();
});

// Load branches from API
async function loadBranches() {
    try {
        const response = await fetch(`${API_BASE}/branches`);
        const branches = await response.json();
        const select = document.getElementById('branch_code');
        branches.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.code;
            option.textContent = `${branch.code} - ${branch.name}`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

// Load positions from localStorage or database
function loadPositions() {
    const bank = document.getElementById('bank_code').value;
    const saved = localStorage.getItem('chequePositions');

    if (saved) {
        const positions = JSON.parse(saved);
        applyPositions(positions);
    } else {
        applyPositions(defaultPositions);
    }
}

// Apply positions to elements
function applyPositions(positions) {
    Object.keys(positions).forEach(id => {
        const element = document.getElementById(id);
        if (element && positions[id]) {
            const props = positions[id];
            if (props.top !== undefined) element.style.top = props.top + 'px';
            if (props.left !== undefined) {
                element.style.left = props.left + 'px';
                element.style.right = 'auto';
            }
            if (props.right !== undefined) {
                element.style.right = props.right + 'px';
                element.style.left = 'auto';
            }
            if (props.fontSize) element.style.fontSize = props.fontSize + 'px';
            if (props.color) element.style.color = props.color;
            if (props.bold !== undefined) element.style.fontWeight = props.bold ? 'bold' : 'normal';
        }
    });
}

// Load bank template
async function loadBankTemplate() {
    const bank = document.getElementById('bank_code').value;
    if (bank === 'custom') {
        loadPositions();
        return;
    }

    // Try to load from API first
    let template = null;
    try {
        const response = await fetch(`${API_BASE}/templates`);
        const templates = await response.json();
        const found = templates.find(t => t.bank === bank);
        if (found) template = found.template_json;
    } catch (error) {
        console.error('Error loading template from API:', error);
    }

    // Fallback to predefined templates
    if (!template) template = bankTemplates[bank];
    if (template) applyPositions(template);
}

// Setup drag and drop
function setupDragAndDrop() {
    const draggables = document.querySelectorAll('.draggable');

    draggables.forEach(element => {
        element.addEventListener('mousedown', function(e) {
            draggedElement = element;
            const rect = element.getBoundingClientRect();
            const parentRect = element.parentElement.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;

            // Select element
            document.querySelectorAll('.draggable').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            selectedElement = element;

            e.preventDefault();
        });
    });

    document.addEventListener('mousemove', function(e) {
        if (draggedElement) {
            const parent = draggedElement.parentElement;
            const parentRect = parent.getBoundingClientRect();

            let x = e.clientX - parentRect.left - offsetX;
            let y = e.clientY - parentRect.top - offsetY;

            // Boundary check
            x = Math.max(0, Math.min(x, parentRect.width - draggedElement.offsetWidth));
            y = Math.max(0, Math.min(y, parentRect.height - draggedElement.offsetHeight));

            draggedElement.style.left = x + 'px';
            draggedElement.style.top = y + 'px';
            draggedElement.style.right = 'auto';
        }
    });

    document.addEventListener('mouseup', function() {
        if (draggedElement) {
            savePositions();
            draggedElement = null;
        }
    });
}

// Save positions to localStorage
function savePositions() {
    const positions = {};
    document.querySelectorAll('.draggable').forEach(el => {
        positions[el.id] = {
            top: parseInt(el.style.top) || 0,
            left: el.style.left !== 'auto' && el.style.left ? parseInt(el.style.left) : undefined,
            right: el.style.right !== 'auto' && el.style.right ? parseInt(el.style.right) : undefined,
            fontSize: parseInt(el.style.fontSize) || 16,
            color: el.style.color || '#000000',
            bold: el.style.fontWeight === 'bold'
        };
    });
    localStorage.setItem('chequePositions', JSON.stringify(positions));
}

// Set today's date
function setTodayDate() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').value = today;
    updateDateDisplay();
}

// Update displays when inputs change
document.getElementById('date')?.addEventListener('change', updateDateDisplay);
document.getElementById('payee')?.addEventListener('input', updatePayeeDisplay);
document.getElementById('amount')?.addEventListener('input', updateAmountText);

function updateDateDisplay() {
    const dateInput = document.getElementById('date').value;
    if (dateInput) {
        const date = new Date(dateInput);
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear() + 543; // Thai year
        document.getElementById('dateDisplay').textContent = `${day}/${month}/${year}`;
    }
}

function updatePayeeDisplay() {
    const payee = document.getElementById('payee').value;
    document.getElementById('payeeDisplay').textContent = payee || '<สั่งจ่าย>';
}

function updateAmountText() {
    const amount = document.getElementById('amount').value;
    const parsed = parseFloat(amount.replace(/,/g, ''));

    if (!isNaN(parsed) && parsed > 0) {
        const text = thaiNumberToText(parsed);
        document.getElementById('amount-text').textContent = text;
        document.getElementById('amountText').textContent = text;
        document.getElementById('amountNumber').textContent = `***${parsed.toFixed(2)}***`;
    } else {
        document.getElementById('amount-text').textContent = '';
        document.getElementById('amountText').textContent = '';
        document.getElementById('amountNumber').textContent = '***0.00***';
    }
}

// Thai number to text conversion
function thaiNumberToText(num) {
    const ones = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    const places = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

    if (num === 0) return 'ศูนย์บาทถ้วน';

    let [baht, satang] = num.toFixed(2).split('.');
    baht = parseInt(baht);
    satang = parseInt(satang);

    let result = '';

    // Convert baht
    if (baht > 0) {
        const bahtStr = baht.toString();
        const len = bahtStr.length;

        for (let i = 0; i < len; i++) {
            const digit = parseInt(bahtStr[i]);
            const place = len - i - 1;

            if (digit !== 0) {
                if (digit === 1 && place === 1) {
                    result += 'สิบ';
                } else if (digit === 2 && place === 1) {
                    result += 'ยี่สิบ';
                } else if (digit === 1 && place === 0 && len > 1) {
                    result += 'เอ็ด';
                } else {
                    result += ones[digit] + places[place % 6];
                }
            }
        }
        result += 'บาท';
    }

    // Convert satang
    if (satang > 0) {
        result += thaiNumberToText(satang).replace('บาทถ้วน', '') + 'สตางค์';
    } else {
        result += 'ถ้วน';
    }

    return result;
}

// Toggle visibility
function toggleAcPayee() {
    const checkbox = document.getElementById('show_ac_payee');
    const element = document.getElementById('acPayee');
    element.style.display = checkbox.checked ? 'block' : 'none';
}

function toggleLine() {
    const checkbox = document.getElementById('show_line');
    const element = document.getElementById('lineHolder');
    element.style.display = checkbox.checked ? 'block' : 'none';
}

// Use next cheque number
async function useNextChequeNo() {
    const branch = document.getElementById('branch_code').value;
    if (!branch) {
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาเลือกสาขา',
            text: 'กรุณาเลือกสาขาก่อนขอเลขถัดไป',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/cheques/next?branch=${branch}`);
        const data = await response.json();
        if (data.cheque_number) {
            document.getElementById('cheque_number').value = data.cheque_number;
            saveFormData();
        }
    } catch (error) {
        console.error('Error fetching next cheque number:', error);
    }
}

// Print cheque
async function printCheque() {
    const branch = document.getElementById('branch_code').value;
    const bank = document.getElementById('bank_code').value;
    const chequeNum = document.getElementById('cheque_number').value;
    const date = document.getElementById('date').value;
    const payee = document.getElementById('payee').value;
    const amount = document.getElementById('amount').value;

    if (!branch) {
        Swal.fire({
            title: 'กรุณาเลือกสาขา!',
            text: 'กรุณาเลือกสาขาก่อนพิมพ์เช็ค',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    if (!chequeNum || !date || !payee || !amount) {
        Swal.fire({
            title: 'ข้อมูลไม่ครบถ้วน!',
            text: 'กรุณากรอกข้อมูลให้ครบทุกช่อง',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    // Save to database
    try {
        const response = await fetch(`${API_BASE}/cheques`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                branch_code: branch,
                bank: bank,
                cheque_number: chequeNum,
                date: date,
                payee: payee,
                amount: parseFloat(amount.replace(/,/g, ''))
            })
        });

        if (response.ok) {
            // Update displays before printing
            updateDateDisplay();
            updatePayeeDisplay();
            updateAmountText();

            // Print
            window.print();

            Swal.fire({
                title: 'บันทึกข้อมูลแล้ว!',
                text: 'ข้อมูลการพิมพ์ถูกบันทึกในรายงาน',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

            // Clear form after print
            setTimeout(() => {
                clearForm();
            }, 500);
        }
    } catch (error) {
        console.error('Error saving cheque:', error);
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่สามารถบันทึกข้อมูลได้',
            icon: 'error',
            confirmButtonColor: '#f44336'
        });
    }
}

// Clear form
function clearForm() {
    if (confirm('ต้องการล้างข้อมูลทั้งหมดหรือไม่?')) {
        document.getElementById('cheque-form').reset();
        setTodayDate();
        document.getElementById('payeeDisplay').textContent = '<สั่งจ่าย>';
        document.getElementById('amountText').textContent = '';
        document.getElementById('amountNumber').textContent = '***0.00***';
        document.getElementById('amount-text').textContent = '';
        localStorage.removeItem('chequeFormData');
    }
}

// Save/load form data
function saveFormData() {
    const formData = {
        branch_code: document.getElementById('branch_code').value,
        bank_code: document.getElementById('bank_code').value,
        cheque_number: document.getElementById('cheque_number').value,
        date: document.getElementById('date').value,
        payee: document.getElementById('payee').value,
        amount: document.getElementById('amount').value,
        show_ac_payee: document.getElementById('show_ac_payee').checked,
        show_line: document.getElementById('show_line').checked
    };
    localStorage.setItem('chequeFormData', JSON.stringify(formData));
}

function loadFormData() {
    const saved = localStorage.getItem('chequeFormData');
    if (saved) {
        const data = JSON.parse(saved);
        Object.keys(data).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = data[key];
                } else {
                    element.value = data[key];
                }
            }
        });
        updateDateDisplay();
        updatePayeeDisplay();
        updateAmountText();
    }
}

// Auto-save form data
['branch_code', 'bank_code', 'cheque_number', 'date', 'payee', 'amount'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('change', saveFormData);
        element.addEventListener('input', saveFormData);
    }
});

// Payee Autocomplete
let autocompleteTimeout;
const payeeInput = document.getElementById('payee');
const autocompleteDiv = document.getElementById('payee-autocomplete');

payeeInput.addEventListener('input', function() {
    clearTimeout(autocompleteTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
        autocompleteDiv.classList.add('hidden');
        return;
    }

    autocompleteTimeout = setTimeout(async () => {
        try {
            const branch = document.getElementById('branch_code').value;
            const url = `${API_BASE}/payees?q=${encodeURIComponent(query)}&limit=10${branch ? '&branch=' + branch : ''}`;
            const response = await fetch(url);
            const payees = await response.json();

            if (payees.length === 0) {
                autocompleteDiv.classList.add('hidden');
                return;
            }

            autocompleteDiv.innerHTML = payees.map(payee => `
                <div class="payee-item px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white"
                     onclick="selectPayee('${payee.replace(/'/g, "\\'")}')">
                    ${payee}
                </div>
            `).join('');

            autocompleteDiv.classList.remove('hidden');
        } catch (error) {
            console.error('Error fetching payees:', error);
        }
    }, 300);
});

function selectPayee(name) {
    payeeInput.value = name;
    autocompleteDiv.classList.add('hidden');
    updatePayeeDisplay();
    saveFormData();
}

// Hide autocomplete when clicking outside
document.addEventListener('click', function(e) {
    if (!payeeInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
        autocompleteDiv.classList.add('hidden');
    }
});

// Hide autocomplete on escape key
payeeInput.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        autocompleteDiv.classList.add('hidden');
    }
});
</script>
@endpush
