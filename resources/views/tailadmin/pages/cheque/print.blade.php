@extends('tailadmin.layouts.app')

@section('title', 'พิมพ์เช็ค - ' . config('app.name'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    font-weight: bold !important;
    font-size: 18px !important;
    transform: rotate(-40deg);
    color: #ff0000 !important;
    text-decoration: overline underline !important;
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
    /* Hide all page content */
    body > * {
        display: none !important;
    }

    /* Show only cheque preview */
    body .cheque-preview {
        display: block !important;
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 800px !important;
        height: 350px !important;
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    /* Show all children */
    .cheque-preview * {
        display: block !important;
    }

    /* Remove drag borders */
    .draggable {
        border: none !important;
        background: none !important;
    }

    /* Ensure text styling is preserved */
    .ac-payee {
        display: block !important;
        font-weight: bold !important;
        font-size: 18px !important;
        transform: rotate(-40deg) !important;
        color: #ff0000 !important;
        text-decoration: overline underline !important;
    }

    .line-holder {
        display: block !important;
        font-size: 20px !important;
        font-weight: bold !important;
    }

    /* Ensure all text elements are visible */
    .draggable {
        display: block !important;
        position: absolute !important;
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

    <div class="grid grid-cols-1 gap-6">
        <!-- Top: Form (แนวนอน) -->
        <div class="w-full">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📝 ข้อมูลเช็ค</h3>

                <form id="cheque-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <input type="text" id="date" name="date" required readonly
                            placeholder="เลือกวันที่"
                            class="w-full rounded border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 cursor-pointer" />
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
                    <div class="flex gap-2 md:col-span-2 lg:col-span-4">
                        <button type="button" onclick="printCheque()" class="flex-1 rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                            🖨️ พิมพ์เช็ค
                        </button>
                        <button type="button" onclick="clearForm()" class="flex-1 rounded border border-gray-300 px-6 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            🔄 เริ่มใหม่
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bottom: Preview -->
        <div class="w-full">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// API Base URL
const API_BASE = '/api';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// Debug CSRF Token
console.log('CSRF Token loaded:', CSRF_TOKEN ? 'Found' : 'NOT FOUND');
if (!CSRF_TOKEN) {
    console.error('⚠️ WARNING: CSRF Token is missing! Check if meta tag exists in layout.');
}

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
    initializeDatePicker();
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

// Initialize Flatpickr Date Picker
function initializeDatePicker() {
    flatpickr("#date", {
        dateFormat: "d/m/Y", // Display format
        altInput: true,
        altFormat: "d/m/Y", // Alt display format
        defaultDate: new Date(),
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
                longhand: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
            },
            months: {
                shorthand: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
                longhand: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
            },
        },
        onChange: function(selectedDates, dateStr, instance) {
            updateDateDisplay(dateStr);
        }
    });

    // Set initial display
    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1;
    const year = today.getFullYear();
    const dateStr = `${day}/${month}/${year}`;
    document.getElementById('date').value = dateStr;
    updateDateDisplay(dateStr);
}

// Update displays when inputs change
document.getElementById('payee')?.addEventListener('input', updatePayeeDisplay);
document.getElementById('amount')?.addEventListener('input', updateAmountText);

function updateDateDisplay(dateStr) {
    if (!dateStr) {
        dateStr = document.getElementById('date').value;
    }

    if (dateStr) {
        // Parse d/m/Y format
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const year = parseInt(parts[2]) + 543; // Convert to Thai year
            const displayDate = `${day}${month}${year}`;
            // Add spaces between each digit
            const spacedDate = displayDate.split('').join('  ');
            document.getElementById('dateDisplay').textContent = spacedDate;
        }
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
        // Format number with commas
        const formatted = parsed.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('amountNumber').textContent = `***${formatted}***`;
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
        result = convertIntegerToThai(baht, ones, places) + 'บาท';
    }

    // Convert satang
    if (satang > 0) {
        result += convertIntegerToThai(satang, ones, places) + 'สตางค์';
    } else {
        result += 'ถ้วน';
    }

    return result;
}

function convertIntegerToThai(number, ones, places) {
    if (number === 0) return '';

    const numStr = number.toString();
    const len = numStr.length;
    let result = '';

    // Handle millions
    if (len > 6) {
        const millions = parseInt(numStr.substring(0, len - 6));
        result += convertIntegerToThai(millions, ones, places) + 'ล้าน';
        number = number % 1000000;
    }

    const numArray = number.toString().split('').map(d => parseInt(d));
    const positions = numArray.length;

    for (let i = 0; i < positions; i++) {
        const digit = numArray[i];
        const position = positions - i - 1;

        if (digit === 0) continue;

        if (position === 5) { // แสน
            result += ones[digit] + 'แสน';
        } else if (position === 4) { // หมื่น
            result += ones[digit] + 'หมื่น';
        } else if (position === 3) { // พัน
            result += ones[digit] + 'พัน';
        } else if (position === 2) { // ร้อย
            result += ones[digit] + 'ร้อย';
        } else if (position === 1) { // สิบ
            if (digit === 1) {
                result += 'สิบ';
            } else if (digit === 2) {
                result += 'ยี่สิบ';
            } else {
                result += ones[digit] + 'สิบ';
            }
        } else { // หน่วย
            if (digit === 1 && positions > 1) {
                result += 'เอ็ด';
            } else {
                result += ones[digit];
            }
        }
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
    const dateInput = document.getElementById('date').value;
    const payee = document.getElementById('payee').value;
    const amount = document.getElementById('amount').value;

    if (!chequeNum || !dateInput || !payee || !amount) {
        Swal.fire({
            title: 'ข้อมูลไม่ครบถ้วน!',
            text: 'กรุณากรอกข้อมูลให้ครบทุกช่อง',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    // Check CSRF token
    if (!CSRF_TOKEN) {
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่พบ CSRF Token กรุณารีเฟรชหน้าใหม่',
            icon: 'error',
            confirmButtonColor: '#f44336'
        });
        return;
    }

    // Convert date from d/m/Y to Y-m-d for database
    let formattedDate = dateInput;
    if (dateInput.includes('/')) {
        const parts = dateInput.split('/');
        if (parts.length === 3) {
            const [day, month, year] = parts;
            formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }
    }

    console.log('Date conversion:', dateInput, '=>', formattedDate);

    // Update displays before printing
    updateDateDisplay();
    updatePayeeDisplay();
    updateAmountText();

    // Save to database FIRST, then print
    try {
        const response = await fetch(`${API_BASE}/cheques`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                branch_code: branch || null,
                bank: bank,
                cheque_number: chequeNum,
                date: formattedDate,
                payee: payee,
                amount: parseFloat(amount.replace(/,/g, ''))
            })
        });

        const data = await response.json();

        if (!response.ok) {
            console.error('Server error:', data);
            throw new Error(data.message || 'Failed to save cheque data');
        }

        console.log('Cheque saved with ID:', data.id);

        // Show success message
        Swal.fire({
            title: 'บันทึกสำเร็จ!',
            text: 'ข้อมูลเช็คถูกบันทึกแล้ว กำลังเปิดหน้าพิมพ์...',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });

        // Print after saving
        setTimeout(() => {
            window.print();
        }, 500);

    } catch (error) {
        console.error('Error saving cheque:', error);

        // Ask user if they want to print anyway
        const result = await Swal.fire({
            title: 'ไม่สามารถบันทึกข้อมูลได้!',
            text: 'ต้องการพิมพ์เช็คต่อไปหรือไม่?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'พิมพ์ต่อ',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            window.print();
        }
    }
}


// Clear form
function clearForm() {
    if (confirm('ต้องการล้างข้อมูลทั้งหมดหรือไม่?')) {
        document.getElementById('cheque-form').reset();
        initializeDatePicker(); // Re-initialize with today's date
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
