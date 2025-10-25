@extends('tailadmin.layouts.app')

@section('title', 'พิมพ์เช็ค - ' . config('app.name'))

@push('styles')
<link href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
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

/* Date should respect multiple spaces for box-fit */
#dateDisplay { white-space: pre; }

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
    /* Hide everything first */
    * {
        visibility: hidden !important;
    }

    /* Reset body */
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }

    /* Show only print container and its contents */
    .print-cheque-container,
    .print-cheque-container *,
    .print-cheque-container * * {
        visibility: visible !important;
    }

    /* Position the print container at top-left */
    .print-cheque-container {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: auto !important;
        height: auto !important;
    }

    /* Style the cheque preview box */
    #chequePreview {
        position: relative !important;
        width: 800px !important;
        height: 350px !important;
        border: 1px solid #000 !important;
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Draggable elements - absolutely positioned */
    .draggable {
        position: absolute !important;
        border: none !important;
        background: transparent !important;
        white-space: nowrap !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Preserve spaces in date during print */
    #dateDisplay { white-space: pre !important; }

    /* A/C PAYEE with red color */
    #acPayee {
        font-weight: bold !important;
        font-size: 18px !important;
        transform: rotate(-40deg) !important;
        transform-origin: left top !important;
        color: #ff0000 !important;
        text-decoration: overline underline !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Text elements - ensure black color */
    #dateDisplay,
    #payeeDisplay,
    #amountText,
    #amountNumber,
    #lineHolder {
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Bold elements */
    #amountNumber,
    #lineHolder {
        font-weight: bold !important;
    }

    #lineHolder {
        font-size: 20px !important;
    }

    /* Hide workspace background */
    .cheque-workspace {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
    }

    .info-badge {
        display: none !important;
    }
}

@page {
    size: A4 portrait;
    margin: 0;
}

/* Additional print fixes */
@media print {
    /* Prevent page breaks */
    html, body {
        height: auto !important;
        overflow: visible !important;
    }

    /* Hide all other page content */
    body > *:not(:has(.print-cheque-container)) {
        display: none !important;
    }

    /* Ensure single page only */
    .print-cheque-container {
        page-break-before: avoid !important;
        page-break-after: avoid !important;
    }

    /* Hide all parent wrappers */
    .flex,
    .grid,
    main,
    [x-data],
    .relative {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
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
                                onblur="loadChequeByNumber()"
                                class="flex-1 rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                            <button type="button" onclick="useNextChequeNo()" class="rounded bg-gray-100 px-4 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700" title="เลขถัดไป">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">กรอกเลขที่เช็คเดิมเพื่อโหลดข้อมูล</p>
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

                <!-- Quick Controls: Date spacing + Selected font size + Print offset -->
                <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <label for="date_spacing" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Spaces between date characters</label>
                        <input id="date_spacing" type="range" min="0" max="8" step="1" value="3" class="w-full"
                               oninput="updateDateSpacingLabel(this.value)" onchange="saveDateSpacing(this.value); updateDateDisplay();" />
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Spaces per character: <span id="date_spacing_value">3</span></div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Font size (selected element)</span>
                            <span id="font_size_value" class="text-[11px] text-gray-500 dark:text-gray-400">-</span>
                        </div>
                        <input id="font_size_slider" type="range" min="10" max="40" step="1" value="18" class="w-full" disabled
                               oninput="onFontSizeSlide(this.value)" onchange="onFontSizeCommit(this.value)" />
                        <div class="mt-2">
                            <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                                <input id="bold_toggle" type="checkbox" class="rounded border-gray-300"
                                       onchange="onBoldToggle(this.checked)" disabled>
                                <span>Bold</span>
                            </label>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Print offset (px)</div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="print_offset_x" class="block text-[11px] text-gray-500 dark:text-gray-400">Offset X</label>
                                <input id="print_offset_x" type="number" step="1" value="0"
                                       class="w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-700"
                                       oninput="savePrintOffsets()" />
                            </div>
                            <div>
                                <label for="print_offset_y" class="block text-[11px] text-gray-500 dark:text-gray-400">Offset Y</label>
                                <input id="print_offset_y" type="number" step="1" value="0"
                                       class="w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-700"
                                       oninput="savePrintOffsets()" />
                            </div>
                        </div>
                        <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">ใช้สำหรับชดเชยความคลาดเคลื่อนของเครื่องพิมพ์</p>
                    </div>
                </div>

                <!-- Wrapper for print -->
                <div class="print-cheque-container">
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
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
            if (id === 'dateDisplay' && props.letterSpacing !== undefined) {
                element.style.letterSpacing = (props.letterSpacing || 0) + 'px';
            }
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
    if (template) {
        applyPositions(template);
        // Persist applied template locally for immediate reuse
        localStorage.setItem('chequePositions', JSON.stringify(template));
    }
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

            // Sync font size slider with selected element
            try {
                const slider = document.getElementById('font_size_slider');
                const label = document.getElementById('font_size_value');
                const boldToggle = document.getElementById('bold_toggle');
                if (slider && label) {
                    const computed = window.getComputedStyle(element);
                    const size = parseInt(computed.fontSize) || 16;
                    slider.disabled = false;
                    slider.value = size;
                    label.textContent = size + 'px';
                }
                if (boldToggle) {
                    const computed = window.getComputedStyle(element);
                    boldToggle.disabled = false;
                    boldToggle.checked = (computed.fontWeight === 'bold' || parseInt(computed.fontWeight) >= 700);
                }
            } catch (err) { console.warn('Failed to sync font size slider', err); }

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

// Save positions (local + auto-save to server when bank selected)
let saveTemplateTimeout = null;
function collectPositions() {
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
    return positions;
}

function savePositions() {
    const positions = collectPositions();
    localStorage.setItem('chequePositions', JSON.stringify(positions));

    const bank = document.getElementById('bank_code').value;
    if (bank && bank !== 'custom') {
        clearTimeout(saveTemplateTimeout);
        saveTemplateTimeout = setTimeout(() => savePositionsToServer(bank, positions), 400);
    }
}

async function savePositionsToServer(bank, positions) {
    try {
        const res = await fetch(`${API_BASE}/templates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ bank, template_json: positions })
        });
        if (!res.ok) {
            console.warn('Template auto-save failed:', res.status, res.statusText);
        } else {
            console.log('Template auto-saved for bank:', bank);
        }
    } catch (e) {
        console.error('Error auto-saving template:', e);
    }
}

// Font-size quick control
function onFontSizeSlide(val) {
    const v = parseInt(val);
    const label = document.getElementById('font_size_value');
    if (label) label.textContent = (isNaN(v) ? '-' : (v + 'px'));
    if (!selectedElement || isNaN(v)) return;
    selectedElement.style.fontSize = v + 'px';
}

function onFontSizeCommit(val) {
    onFontSizeSlide(val);
    savePositions();
}

function onBoldToggle(checked) {
    if (!selectedElement) return;
    selectedElement.style.fontWeight = checked ? 'bold' : 'normal';
    savePositions();
}

// Keyboard nudging for selected element
document.addEventListener('keydown', function(e) {
    if (!selectedElement) return;
    // Ignore when typing in inputs
    const tag = (e.target && e.target.tagName || '').toLowerCase();
    if (tag === 'input' || tag === 'textarea') return;

    const step = e.shiftKey ? 5 : 1;
    let dx = 0, dy = 0;
    if (e.key === 'ArrowLeft') { dx = -step; }
    else if (e.key === 'ArrowRight') { dx = step; }
    else if (e.key === 'ArrowUp') { dy = -step; }
    else if (e.key === 'ArrowDown') { dy = step; }
    else { return; }

    e.preventDefault();

    // Ensure base position from computed rect if left/top not set
    const parent = selectedElement.parentElement;
    const parentRect = parent.getBoundingClientRect();
    const rect = selectedElement.getBoundingClientRect();
    let left = parseInt(selectedElement.style.left);
    let top = parseInt(selectedElement.style.top);
    if (isNaN(left) || isNaN(top)) {
        left = rect.left - parentRect.left;
        top = rect.top - parentRect.top;
    }
    left += dx; top += dy;

    // Constrain within parent
    left = Math.max(0, Math.min(left, parentRect.width - selectedElement.offsetWidth));
    top = Math.max(0, Math.min(top, parentRect.height - selectedElement.offsetHeight));

    selectedElement.style.left = Math.round(left) + 'px';
    selectedElement.style.top = Math.round(top) + 'px';
    selectedElement.style.right = 'auto';

    savePositions();
});

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
            // Add dynamic spaces between each character
            const spacesCount = parseInt(localStorage.getItem('chequeDateSpacing') || '3');
            const spacer = ' '.repeat(isNaN(spacesCount) ? 3 : spacesCount);
            const spacedDate = displayDate.split('').join(spacer);
            const dd = document.getElementById('dateDisplay');
            dd.textContent = spacedDate;
            // Ensure spaces are respected
            dd.style.whiteSpace = 'pre';
        }
    }
}

// Date spacing helpers
function saveDateSpacing(val) {
    const n = parseInt(val);
    localStorage.setItem('chequeDateSpacing', isNaN(n) ? '3' : String(n));
}

function updateDateSpacingLabel(val) {
    const el = document.getElementById('date_spacing_value');
    if (el) el.textContent = String(val);
}

// Initialize quick controls after DOM ready
document.addEventListener('DOMContentLoaded', function() {
    try {
        const spacingInput = document.getElementById('date_spacing');
        const saved = parseInt(localStorage.getItem('chequeDateSpacing') || '3');
        if (spacingInput) {
            spacingInput.value = isNaN(saved) ? 3 : saved;
            updateDateSpacingLabel(spacingInput.value);
        }
        // Load print offsets
        const off = getPrintOffsets();
        const ox = document.getElementById('print_offset_x');
        const oy = document.getElementById('print_offset_y');
        if (ox) ox.value = off.x;
        if (oy) oy.value = off.y;
    } catch {}
});

// Print offset helpers
function getPrintOffsets() {
    const x = parseInt(localStorage.getItem('chequePrintOffsetX') || '0');
    const y = parseInt(localStorage.getItem('chequePrintOffsetY') || '0');
    return { x: isNaN(x) ? 0 : x, y: isNaN(y) ? 0 : y };
}

function savePrintOffsets() {
    const ox = parseInt(document.getElementById('print_offset_x')?.value || '0');
    const oy = parseInt(document.getElementById('print_offset_y')?.value || '0');
    localStorage.setItem('chequePrintOffsetX', isNaN(ox) ? '0' : String(ox));
    localStorage.setItem('chequePrintOffsetY', isNaN(oy) ? '0' : String(oy));
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

// Load cheque data by number
async function loadChequeByNumber() {
    const chequeNumber = document.getElementById('cheque_number').value.trim();

    if (!chequeNumber) {
        // Reset flags when field is empty
        isExistingCheque = false;
        loadedChequeId = null;
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/cheques/number/${encodeURIComponent(chequeNumber)}`);
        const result = await response.json();

        // Handle 404 - cheque not found (this is OK for new cheques)
        if (response.status === 404 || !result.success) {
            console.log('Cheque number not found - will create new cheque');
            // Reset flags for new cheque
            isExistingCheque = false;
            loadedChequeId = null;
            return;
        }

        // Handle other errors
        if (!response.ok) {
            console.error('Error loading cheque:', result);
            return;
        }

        // Success - found existing cheque
        if (result.success && result.data) {
            const cheque = result.data;

            // Show confirmation before loading
            const confirm = await Swal.fire({
                title: 'พบข้อมูลเช็คเดิม',
                html: `
                    <div class="text-left">
                        <p><strong>เลขที่:</strong> ${cheque.cheque_number}</p>
                        <p><strong>วันที่:</strong> ${cheque.date}</p>
                        <p><strong>จ่ายให้:</strong> ${cheque.payee}</p>
                        <p><strong>จำนวน:</strong> ${parseFloat(cheque.amount).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'โหลดข้อมูล',
                cancelButtonText: 'ยกเลิก'
            });

            if (confirm.isConfirmed) {
                // Mark as existing cheque
                isExistingCheque = true;
                loadedChequeId = cheque.id;

                // Populate form with existing data
                if (cheque.branch_code) {
                    document.getElementById('branch_code').value = cheque.branch_code;
                }
                if (cheque.bank) {
                    document.getElementById('bank_code').value = cheque.bank;
                    await loadBankTemplate(); // Load bank template
                }

                // Convert date from Y-m-d to d/m/Y
                if (cheque.date) {
                    const dateParts = cheque.date.split('-');
                    if (dateParts.length === 3) {
                        const [year, month, day] = dateParts;
                        const displayDate = `${parseInt(day)}/${parseInt(month)}/${year}`;
                        document.getElementById('date').value = displayDate;
                        updateDateDisplay(displayDate);
                    }
                }

                document.getElementById('payee').value = cheque.payee || '';
                document.getElementById('amount').value = parseFloat(cheque.amount).toFixed(2);

                // Update displays
                updatePayeeDisplay();
                updateAmountText();

                Swal.fire({
                    title: 'โหลดข้อมูลสำเร็จ',
                    text: 'ข้อมูลเช็คถูกโหลดแล้ว',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    } catch (error) {
        console.error('Error loading cheque:', error);
        // Don't show error for 404 - it means this is a new cheque
    }
}

// Track if cheque was loaded from database
let isExistingCheque = false;
let loadedChequeId = null;

// Print cheque
async function printCheque() {
    console.log('=== printCheque() called ===');

    const branch = document.getElementById('branch_code').value;
    const bank = document.getElementById('bank_code').value;
    const chequeNum = document.getElementById('cheque_number').value;
    const dateInput = document.getElementById('date').value;
    const payee = document.getElementById('payee').value;
    const amount = document.getElementById('amount').value;

    console.log('Form data:', { branch, bank, chequeNum, dateInput, payee, amount });

    if (!chequeNum || !dateInput || !payee || !amount) {
        console.warn('Validation failed: Missing required fields');
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
        console.error('CSRF Token is missing!');
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่พบ CSRF Token กรุณารีเฟรชหน้าใหม่',
            icon: 'error',
            confirmButtonColor: '#f44336'
        });
        return;
    }

    console.log('CSRF Token verified:', CSRF_TOKEN.substring(0, 10) + '...');

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
    console.log('Updating displays...');
    updateDateDisplay();
    updatePayeeDisplay();
    updateAmountText();

    // If this is an existing cheque, just print (don't save again)
    if (isExistingCheque && loadedChequeId) {
        console.log('Printing existing cheque, ID:', loadedChequeId);
        Swal.fire({
            title: 'พิมพ์เช็คซ้ำ',
            text: 'กำลังพิมพ์เช็คที่บันทึกไว้แล้ว...',
            icon: 'info',
            timer: 1000,
            showConfirmButton: false
        });

        setTimeout(() => {
            console.log('Opening print window for existing cheque...');
            forcePrint();
        }, 500);
        return;
    }

    console.log('This is a new cheque, will save before printing...');

    // Save to database FIRST, then print (for new cheques only)
    try {
        const requestData = {
            branch_code: branch || null,
            bank: bank,
            cheque_number: chequeNum,
            date: formattedDate,
            payee: payee,
            amount: parseFloat(amount.replace(/,/g, ''))
        };

        console.log('Sending cheque data:', requestData);

        const response = await fetch(`${API_BASE}/cheques`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();

        if (!response.ok) {
            console.error('Server error response:', {
                status: response.status,
                statusText: response.statusText,
                data: data
            });

            // Show detailed error for debugging
            if (response.status === 422) {
                console.error('Validation errors:', data.errors);

                // Check if it's a duplicate error
                if (data.message && (data.message.includes('already exists') || data.message.includes('already been taken'))) {
                    console.log('Duplicate cheque number detected, attempting to load existing data...');

                    // Try to load the existing cheque data
                    try {
                        const loadResponse = await fetch(`${API_BASE}/cheques/number/${encodeURIComponent(chequeNum)}`);
                        const loadResult = await loadResponse.json();

                        if (loadResult.success && loadResult.data) {
                            const existingCheque = loadResult.data;
                            console.log('Loaded existing cheque:', existingCheque);

                            // Mark as existing cheque
                            isExistingCheque = true;
                            loadedChequeId = existingCheque.id;

                            // Populate form with existing data FIRST
                            if (existingCheque.branch_code) {
                                document.getElementById('branch_code').value = existingCheque.branch_code;
                            }
                            if (existingCheque.bank) {
                                document.getElementById('bank_code').value = existingCheque.bank;
                                await loadBankTemplate(); // Load bank template
                            }

                            // Convert date from ISO to d/m/Y format
                            if (existingCheque.date) {
                                const dateObj = new Date(existingCheque.date);
                                const day = dateObj.getDate();
                                const month = dateObj.getMonth() + 1;
                                const year = dateObj.getFullYear();
                                const displayDate = `${day}/${month}/${year}`;
                                document.getElementById('date').value = displayDate;
                            }

                            document.getElementById('payee').value = existingCheque.payee || '';
                            document.getElementById('amount').value = parseFloat(existingCheque.amount).toFixed(2);

                            // Update all displays
                            updateDateDisplay();
                            updatePayeeDisplay();
                            updateAmountText();

                            const result = await Swal.fire({
                                title: 'พบข้อมูลเช็คเดิม',
                                html: `
                                    <div style="text-align: left;">
                                        <p><strong>เลขที่:</strong> ${existingCheque.cheque_number}</p>
                                        <p><strong>วันที่:</strong> ${existingCheque.date}</p>
                                        <p><strong>จ่ายให้:</strong> ${existingCheque.payee}</p>
                                        <p><strong>จำนวน:</strong> ${parseFloat(existingCheque.amount).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท</p>
                                        <p style="margin-top: 10px; color: #666;">ข้อมูลถูกโหลดลงฟอร์มแล้ว ต้องการพิมพ์เช็คนี้หรือไม่?</p>
                                    </div>
                                `,
                                icon: 'info',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'พิมพ์เช็ค',
                                cancelButtonText: 'ยกเลิก'
                            });

                            if (result.isConfirmed) {
                                console.log('User confirmed to print existing cheque');

                                // Data is already loaded and displays are updated
                                setTimeout(() => {
                                    console.log('Opening print window for duplicate cheque...');
                                    forcePrint();
                                }, 500);
                            } else {
                                console.log('User cancelled printing, resetting flags');
                                // User cancelled, reset flags
                                isExistingCheque = false;
                                loadedChequeId = null;
                            }
                        } else {
                            throw new Error('Could not load existing cheque data');
                        }
                    } catch (loadError) {
                        console.error('Error loading existing cheque:', loadError);
                        await Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: 'ไม่สามารถโหลดข้อมูลเช็คเดิมได้',
                            icon: 'error',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                    return;
                }

                // Other validation errors
                const errorMessages = [];
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        errorMessages.push(`${field}: ${data.errors[field].join(', ')}`);
                    });
                }

                await Swal.fire({
                    title: 'ข้อมูลไม่ถูกต้อง!',
                    html: `<div style="text-align: left;">
                        <p><strong>กรุณาตรวจสอบข้อมูล:</strong></p>
                        <ul style="margin-top: 10px;">
                            ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                        </ul>
                    </div>`,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            throw new Error(data.message || 'Failed to save cheque data');
        }

        console.log('✓ Cheque saved successfully with ID:', data.id);

        // Mark as existing cheque to prevent duplicate saves
        isExistingCheque = true;
        loadedChequeId = data.id;

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
            console.log('Opening print window...');
            forcePrint();
        }, 500);

    } catch (error) {
        console.error('✗ Error saving cheque:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack
        });

        // Ask user if they want to print anyway
        const result = await Swal.fire({
            title: 'ไม่สามารถบันทึกข้อมูลได้!',
            html: `
                <div style="text-align: left;">
                    <p><strong>ข้อผิดพลาด:</strong> ${error.message}</p>
                    <p style="margin-top: 10px;">ต้องการพิมพ์เช็คต่อไปหรือไม่?</p>
                    <p style="font-size: 12px; color: #666; margin-top: 10px;">
                        หมายเหตุ: ข้อมูลจะไม่ถูกบันทึกในระบบ
                    </p>
                </div>
            `,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'พิมพ์ต่อ',
            cancelButtonText: 'ยกเลิก'
        });

        if (result.isConfirmed) {
            console.log('User chose to print anyway...');
            forcePrint();
        } else {
            console.log('User cancelled printing');
        }
    }
}

// Debug function to test print layout
function debugPrintLayout() {
    console.log('=== Debug Print Layout ===');
    const container = document.querySelector('.print-cheque-container');
    const preview = document.getElementById('chequePreview');
    const draggables = document.querySelectorAll('.draggable');

    console.log('Container:', {
        exists: !!container,
        dimensions: container ? `${container.offsetWidth}x${container.offsetHeight}` : 'N/A',
        position: container ? window.getComputedStyle(container).position : 'N/A'
    });

    console.log('Preview:', {
        exists: !!preview,
        dimensions: preview ? `${preview.offsetWidth}x${preview.offsetHeight}` : 'N/A',
        position: preview ? window.getComputedStyle(preview).position : 'N/A'
    });

    console.log('Draggable elements:', draggables.length);
    draggables.forEach(el => {
        const style = window.getComputedStyle(el);
        console.log(`${el.id}:`, {
            text: el.textContent.trim(),
            top: el.style.top,
            left: el.style.left,
            right: el.style.right,
            fontSize: style.fontSize,
            color: style.color,
            display: style.display,
            visibility: style.visibility,
            position: style.position
        });
    });
}

// Make debug function available globally
window.debugPrintLayout = debugPrintLayout;

// Test print preview without actually printing
function testPrintPreview() {
    console.log('=== Testing Print Preview ===');

    // Add a temporary print-mode class to body
    document.body.classList.add('print-preview-mode');

    // Apply print styles temporarily
    const style = document.createElement('style');
    style.id = 'test-print-preview';
    style.textContent = `
        body.print-preview-mode {
            margin: 0 !important;
            padding: 0 !important;
            background: #f0f0f0 !important;
        }

        body.print-preview-mode > *:not(:has(.print-cheque-container)) {
            display: none !important;
        }

        body.print-preview-mode .print-cheque-container {
            display: block !important;
            position: fixed !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            padding: 20px !important;
            background: white !important;
            box-shadow: 0 0 50px rgba(0,0,0,0.3) !important;
            z-index: 999999 !important;
        }

        body.print-preview-mode #chequePreview {
            border: 2px solid #000 !important;
        }
    `;
    document.head.appendChild(style);

    console.log('Print preview mode activated. Click anywhere to exit.');

    // Click to exit preview
    const exitPreview = () => {
        document.body.classList.remove('print-preview-mode');
        document.getElementById('test-print-preview')?.remove();
        document.removeEventListener('click', exitPreview);
        console.log('Print preview mode deactivated.');
    };

    setTimeout(() => {
        document.addEventListener('click', exitPreview, { once: true });
    }, 100);
}

window.testPrintPreview = testPrintPreview;

// Force print using simplified approach
function forcePrint() {
    console.log('=== Force Print with Minimal CSS ===');

    // Create a new window with only the cheque
    const printWindow = window.open('', '_blank', 'width=900,height=500');

    const chequePreview = document.getElementById('chequePreview');
    if (!chequePreview) {
        console.error('Cheque preview not found!');
        return;
    }

    // Clone the cheque preview
    const clonedCheque = chequePreview.cloneNode(true);
    // Apply user-defined printer offsets (px)
    try {
        const off = (function(){ const x=parseInt(localStorage.getItem('chequePrintOffsetX')||'0'); const y=parseInt(localStorage.getItem('chequePrintOffsetY')||'0'); return {x:isNaN(x)?0:x, y:isNaN(y)?0:y}; })();
        if (off.x || off.y) {
            clonedCheque.style.transform = `translate(${off.x}px, ${off.y}px)`;
        }
    } catch {}

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>พิมพ์เช็ค</title>
            <style>
                @page {
                    size: A4 portrait;
                    margin: 0;
                }

                body {
                    margin: 0;
                    padding: 0;
                    background: white;
                    font-family: Arial, Tahoma, sans-serif;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                #chequePreview {
                    position: relative;
                    width: 800px;
                    height: 350px;
                    border: none;
                    background: white;
                    margin: 0;
                    padding: 0;
                }

                .draggable {
                    position: absolute;
                    white-space: nowrap;
                    border: none;
                    background: transparent;
                    line-height: 1.2;
                }

                #acPayee {
                    font-weight: bold;
                    font-size: 18px;
                    transform: rotate(-40deg);
                    transform-origin: left top;
                    color: #ff0000 !important;
                    text-decoration: overline underline;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                #dateDisplay,
                #payeeDisplay,
                #amountText,
                #amountNumber,
                #lineHolder {
                    color: #000000;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                /* Preserve spaces in date */
                #dateDisplay { white-space: pre; }

                #amountNumber,
                #lineHolder {
                    font-weight: bold;
                }

                #lineHolder {
                    font-size: 20px;
                }

                .ac-payee {
                    font-weight: bold;
                    font-size: 18px;
                    transform: rotate(-40deg);
                    color: #ff0000;
                    text-decoration: overline underline;
                }

                .line-holder {
                    font-size: 20px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            ` + clonedCheque.outerHTML + `
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        // Uncomment to auto-close after print dialog
                        // setTimeout(function() { window.close(); }, 100);
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `;

    printWindow.document.write(html);

    printWindow.document.close();
    console.log('Print window opened');
}

window.forcePrint = forcePrint;


// Clear form
async function clearForm() {
    const result = await Swal.fire({
        title: 'ยืนยันการล้างข้อมูล',
        text: 'ต้องการล้างข้อมูลทั้งหมดและเริ่มใหม่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ล้างข้อมูล',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        console.log('Clearing form data...');

        // Reset flags
        isExistingCheque = false;
        loadedChequeId = null;

        document.getElementById('cheque-form').reset();
        initializeDatePicker(); // Re-initialize with today's date
        document.getElementById('payeeDisplay').textContent = '<สั่งจ่าย>';
        document.getElementById('amountText').textContent = '';
        document.getElementById('amountNumber').textContent = '***0.00***';
        document.getElementById('amount-text').textContent = '';
        localStorage.removeItem('chequeFormData');

        // Show success message
        Swal.fire({
            title: 'ล้างข้อมูลสำเร็จ!',
            text: 'พร้อมสำหรับการกรอกข้อมูลใหม่',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
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

        // Reset flags when cheque number changes
        if (id === 'cheque_number') {
            element.addEventListener('input', function() {
                isExistingCheque = false;
                loadedChequeId = null;
            });
        }
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
