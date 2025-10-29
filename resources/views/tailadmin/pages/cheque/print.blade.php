@extends('tailadmin.layouts.app')

@section('title', 'ระบบเช็ค - ' . config('app.name'))

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
    width: 890px;
    height: 445px;
    background: white;
    background-size: contain;
    background-position: top left;
    background-repeat: no-repeat;
    border: 2px solid #ddd;
    margin: 20px auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cheque-preview.has-background {
    background-color: transparent;
}

.draggable {
    position: absolute;
    cursor: move;
    user-select: none;
    padding: 0;
    margin: 0;
    border: 1px dashed transparent;
    white-space: nowrap;
    line-height: 1;
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

/* Tabs */
.tab-button {
    padding: 12px 24px;
    border-bottom: 3px solid transparent;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s;
}

.tab-button:hover {
    color: #374151;
    border-bottom-color: #d1d5db;
}

.tab-button.active {
    color: #4f46e5;
    border-bottom-color: #4f46e5;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
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
        border: none !important;
        background: white !important;
        background-image: none !important;
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
        padding: 0 !important;
        margin: 0 !important;
        line-height: 1 !important;
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
    size: 178mm 89mm;  /* BOT Standard Cheque Size */
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
            ระบบเช็ค
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li class="font-medium text-brand-500">ระบบเช็ค</li>
            </ol>
        </nav>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex gap-4">
            <button onclick="switchTab('print')" class="tab-button active" id="tab-print-btn">
                📝 พิมพ์เช็ค
            </button>
            <button onclick="switchTab('designer')" class="tab-button" id="tab-designer-btn">
                🎨 ออกแบบตำแหน่ง
            </button>
        </div>
    </div>

    <!-- Tab Content: Print -->
    <div id="tab-print-content" class="tab-content active">
        <div class="grid grid-cols-1 gap-6">
            <!-- Print Form -->
            @include('tailadmin.pages.cheque.components.print-form')

            <!-- Preview Container (shown in both tabs) -->
            <div class="w-full" id="preview-container">
                @include('tailadmin.pages.cheque.components.cheque-preview')
            </div>
        </div>
    </div>

    <!-- Tab Content: Designer -->
    <div id="tab-designer-content" class="tab-content">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <!-- Designer Controls (Left Sidebar) -->
            @include('tailadmin.pages.cheque.components.designer-form')

            <!-- Preview (Right - 3 columns) -->
            <div class="lg:col-span-3">
                <div class="mb-4 space-y-2">
                    <div class="flex gap-2 flex-wrap">
                        <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                            💡 คลิกที่องค์ประกอบเพื่อแก้ไข
                        </span>
                        <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                            🖱️ ลากเพื่อย้ายตำแหน่ง
                        </span>
                        <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                            ⌨️ ใช้ลูกศรเพื่อปรับละเอียด
                        </span>
                    </div>
                </div>
                <!-- Preview will be moved here when switching to designer tab -->
                <div id="preview-placeholder"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
// =====================================
// Tab Management
// =====================================
function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`tab-${tabName}-btn`).classList.add('active');

    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    document.getElementById(`tab-${tabName}-content`).classList.add('active');

    // Move preview to correct location
    const previewContainer = document.getElementById('preview-container');
    if (!previewContainer) {
        console.error('Preview container not found!');
        return;
    }

    if (tabName === 'print') {
        // Move preview back to print tab
        const printContent = document.getElementById('tab-print-content');
        const printGrid = printContent.querySelector('.grid');
        if (printGrid && !printGrid.contains(previewContainer)) {
            printGrid.appendChild(previewContainer);
        }
        previewContainer.className = 'w-full';
        updatePreviewWithRealData();
    } else if (tabName === 'designer') {
        // Move preview to designer tab
        const designerPlaceholder = document.getElementById('preview-placeholder');
        if (designerPlaceholder && !designerPlaceholder.contains(previewContainer)) {
            designerPlaceholder.appendChild(previewContainer);
        }
        previewContainer.className = '';
        updatePreviewWithSampleData();
        loadChequeBackground(); // Only load background in designer tab
    }
}

// Update preview with real form data (for Print tab)
function updatePreviewWithRealData() {
    updateDateDisplay();
    updatePayeeDisplay();
    updateAmountText();

    // Remove background image in print tab
    const preview = document.getElementById('chequePreview');
    if (preview) {
        preview.style.backgroundImage = '';
        preview.classList.remove('has-background');
    }
}

// Update preview with sample data (for Designer tab)
function updatePreviewWithSampleData() {
    // Load saved positions first
    loadPositions();

    // Set sample date
    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1;
    const year = today.getFullYear() + 543;
    const dateString = `${day}${month}${year}`;
    const spacesCount = parseInt(localStorage.getItem('chequeDateSpacing') || '3');
    const spacer = ' '.repeat(spacesCount);
    const spacedDate = dateString.split('').join(spacer);
    document.getElementById('dateDisplay').textContent = spacedDate;

    // Set sample payee
    document.getElementById('payeeDisplay').textContent = 'บริษัท ตัวอย่าง จำกัด';

    // Set sample amount
    document.getElementById('amountText').textContent = 'หนึ่งพันสองร้อยห้าสิบบาทถ้วน';
    document.getElementById('amountNumber').textContent = '***1,250.00***';

    // Load background for designer
    loadChequeBackground();

    // Also update the bank template selector to match current bank (if any)
    const bankCode = document.getElementById('bank_code');
    const bankTemplate = document.getElementById('bank_template');
    if (bankCode && bankTemplate && bankCode.value) {
        bankTemplate.value = bankCode.value;
    }
}

// =====================================
// Shared Constants & Variables
// =====================================
const API_BASE = '/api';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

console.log('CSRF Token loaded:', CSRF_TOKEN ? 'Found' : 'NOT FOUND');
if (!CSRF_TOKEN) {
    console.error('⚠️ WARNING: CSRF Token is missing! Check if meta tag exists in layout.');
}

// Element selection
let selectedElement = null;
let draggedElement = null;
let offsetX = 0;
let offsetY = 0;

// BOT Standard Cheque Dimensions
const chequeDimensions = {
    standard: { width: 890, height: 445 },  // 178mm x 89mm @ 5px/mm
    scb:      { width: 890, height: 445 },
    kbank:    { width: 890, height: 445 },
    ktb:      { width: 890, height: 445 },
    custom:   { width: 890, height: 445 }
};

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

// Track if cheque was loaded from database
let isExistingCheque = false;
let loadedChequeId = null;

// =====================================
// Initialization
// =====================================
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    loadPositions();
    setupDragAndDrop();
    initializeDatePicker();
    loadFormData();
    loadDateSpacing();
    setupPayeeAutocomplete();
    loadAutoClearSetting();

    // Load saved cheque size or apply default
    const savedSize = localStorage.getItem('currentChequeSize');
    if (savedSize) {
        const dimensions = JSON.parse(savedSize);
        const preview = document.getElementById('chequePreview');
        if (preview) {
            preview.style.width = dimensions.width + 'px';
            preview.style.height = dimensions.height + 'px';
        }
    }

    // Initialize with print tab (real data, no background)
    updatePreviewWithRealData();
});

// =====================================
// Background Image Management (Designer Tab Only)
// =====================================
function handleBackgroundUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('กรุณาเลือกไฟล์รูปภาพ (PNG, JPG, etc.)');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const imageData = e.target.result;
        setChequeBackground(imageData);

        const bank = document.getElementById('bank_template')?.value || 'custom';
        const key = `chequeBackground_${bank}`;
        localStorage.setItem(key, imageData);

        alert('อัปโหลดรูปพื้นหลังสำเร็จ!');
    };
    reader.readAsDataURL(file);
}

function loadBackgroundFromUrl(url) {
    if (!url || url.trim() === '') return;

    try {
        new URL(url);
    } catch {
        alert('URL ไม่ถูกต้อง กรุณาใส่ URL ที่ถูกต้อง');
        return;
    }

    setChequeBackground(url);

    const bank = document.getElementById('bank_template')?.value || 'custom';
    const key = `chequeBackground_${bank}`;
    localStorage.setItem(key, url);

    alert('โหลดรูปพื้นหลังสำเร็จ!');
}

function setChequeBackground(imageSource) {
    const preview = document.getElementById('chequePreview');
    if (preview) {
        preview.style.backgroundImage = `url('${imageSource}')`;
        preview.classList.add('has-background');
    }
}

function removeChequeBackground() {
    const preview = document.getElementById('chequePreview');
    if (preview) {
        preview.style.backgroundImage = '';
        preview.classList.remove('has-background');
    }

    const bank = document.getElementById('bank_template')?.value || 'custom';
    const key = `chequeBackground_${bank}`;
    localStorage.removeItem(key);

    const uploadInput = document.getElementById('cheque_bg_upload');
    const urlInput = document.getElementById('cheque_bg_url');
    if (uploadInput) uploadInput.value = '';
    if (urlInput) urlInput.value = '';

    alert('ลบรูปพื้นหลังสำเร็จ!');
}

function loadChequeBackground() {
    const bank = document.getElementById('bank_template')?.value || document.getElementById('bank_code')?.value || 'custom';
    const key = `chequeBackground_${bank}`;
    const saved = localStorage.getItem(key);

    if (saved) {
        setChequeBackground(saved);
        const urlInput = document.getElementById('cheque_bg_url');
        if (urlInput && !saved.startsWith('data:')) {
            urlInput.value = saved;
        }
    }
}

// =====================================
// Data Loading & API Calls
// =====================================
async function loadBranches() {
    try {
        const response = await fetch(`${API_BASE}/branches`);
        const branches = await response.json();
        const select = document.getElementById('branch_code');
        if (select) {
            branches.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.code;
                option.textContent = `${branch.code} - ${branch.name}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

function loadPositions() {
    const saved = localStorage.getItem('chequePositions');
    if (saved) {
        const positions = JSON.parse(saved);
        applyPositions(positions);
    } else {
        applyPositions(defaultPositions);
    }
}

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

async function loadBankTemplate() {
    const bank = document.getElementById('bank_code')?.value;
    if (!bank) return;

    applyChequeSize(bank);

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
        localStorage.setItem('chequePositions', JSON.stringify(template));
    }
}

async function loadTemplate() {
    const bank = document.getElementById('bank_template')?.value;
    if (!bank) return;

    loadChequeBackground();
    applyChequeSize(bank);

    if (bank === 'custom') {
        loadPositions();
        return;
    }

    // Try API first
    let template = null;
    try {
        const response = await fetch(`${API_BASE}/templates`);
        const templates = await response.json();
        const found = templates.find(t => t.bank === bank);
        if (found) template = found.template_json;
    } catch (error) {
        console.error('Error loading template:', error);
    }

    // Fallback to built-in templates
    if (!template) template = bankTemplates[bank];
    if (template) applyPositions(template);
}

function applyChequeSize(bank) {
    const preview = document.getElementById('chequePreview');
    if (!preview) return;

    const dimensions = chequeDimensions[bank] || chequeDimensions.custom;
    preview.style.width = dimensions.width + 'px';
    preview.style.height = dimensions.height + 'px';

    localStorage.setItem('currentChequeSize', JSON.stringify(dimensions));
    console.log(`Applied ${bank} cheque size: ${dimensions.width}x${dimensions.height}px`);
}

// =====================================
// Drag and Drop
// =====================================
function setupDragAndDrop() {
    const draggables = document.querySelectorAll('.draggable');

    draggables.forEach(element => {
        element.addEventListener('mousedown', function(e) {
            draggedElement = element;
            const rect = element.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;

            // Select element
            document.querySelectorAll('.draggable').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            selectedElement = element;

            // Sync controls (Print tab)
            syncFontSizeSlider();

            // Sync property panel (Designer tab)
            syncPropertyPanel();

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

            // Update property panel if in designer tab
            if (selectedElement === draggedElement) {
                const propX = document.getElementById('prop_x');
                const propY = document.getElementById('prop_y');
                if (propX) propX.value = Math.round(x);
                if (propY) propY.value = Math.round(y);
            }
        }
    });

    document.addEventListener('mouseup', function() {
        if (draggedElement) {
            savePositions();
            draggedElement = null;
        }
    });
}

function syncFontSizeSlider() {
    if (!selectedElement) return;

    try {
        const slider = document.getElementById('font_size_slider');
        const label = document.getElementById('font_size_value');
        const boldToggle = document.getElementById('bold_toggle');

        if (slider && label) {
            const computed = window.getComputedStyle(selectedElement);
            const size = parseInt(computed.fontSize) || 16;
            slider.disabled = false;
            slider.value = size;
            label.textContent = size + 'px';
        }

        if (boldToggle) {
            const computed = window.getComputedStyle(selectedElement);
            boldToggle.disabled = false;
            boldToggle.checked = (computed.fontWeight === 'bold' || parseInt(computed.fontWeight) >= 700);
        }
    } catch (err) {
        console.warn('Failed to sync font size slider', err);
    }
}

function syncPropertyPanel() {
    const elementProps = document.getElementById('element_properties');
    if (!elementProps) return;

    elementProps.style.display = 'block';
    const nameSpan = document.getElementById('selected_element_name');
    if (nameSpan) {
        nameSpan.textContent = selectedElement.getAttribute('data-name');
    }

    // Populate property panel
    const computedStyle = window.getComputedStyle(selectedElement);
    const propX = document.getElementById('prop_x');
    const propY = document.getElementById('prop_y');
    const propFontSize = document.getElementById('prop_font_size');
    const propColor = document.getElementById('prop_color');
    const propBold = document.getElementById('prop_bold');

    if (propX) propX.value = parseInt(selectedElement.style.left) || 0;
    if (propY) propY.value = parseInt(selectedElement.style.top) || 0;
    if (propFontSize) propFontSize.value = parseInt(computedStyle.fontSize);
    if (propColor) propColor.value = rgbToHex(computedStyle.color);
    if (propBold) propBold.checked = computedStyle.fontWeight === 'bold' || computedStyle.fontWeight >= 700;
}

function updateElementProperty() {
    if (!selectedElement) return;

    const x = parseInt(document.getElementById('prop_x')?.value || 0);
    const y = parseInt(document.getElementById('prop_y')?.value || 0);
    const fontSize = parseInt(document.getElementById('prop_font_size')?.value || 16);
    const color = document.getElementById('prop_color')?.value || '#000000';
    const bold = document.getElementById('prop_bold')?.checked || false;

    selectedElement.style.left = x + 'px';
    selectedElement.style.top = y + 'px';
    selectedElement.style.right = 'auto';
    selectedElement.style.fontSize = fontSize + 'px';
    selectedElement.style.color = color;
    selectedElement.style.fontWeight = bold ? 'bold' : 'normal';

    savePositions();
}

function rgbToHex(rgb) {
    const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!match) return '#000000';
    return '#' + [match[1], match[2], match[3]].map(x => {
        const hex = parseInt(x).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    }).join('');
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

    const bank = document.getElementById('bank_code')?.value || document.getElementById('bank_template')?.value;
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

// =====================================
// Font Size & Bold Controls (Print Tab)
// =====================================
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

    left = Math.max(0, Math.min(left, parentRect.width - selectedElement.offsetWidth));
    top = Math.max(0, Math.min(top, parentRect.height - selectedElement.offsetHeight));

    selectedElement.style.left = Math.round(left) + 'px';
    selectedElement.style.top = Math.round(top) + 'px';
    selectedElement.style.right = 'auto';

    savePositions();
});

// =====================================
// Date Picker & Display Updates
// =====================================
function initializeDatePicker() {
    flatpickr("#date", {
        dateFormat: "d/m/Y",
        altInput: true,
        altFormat: "d/m/Y",
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
    const dateInput = document.getElementById('date');
    if (dateInput) dateInput.value = dateStr;
    updateDateDisplay(dateStr);
}

function updateDateDisplay(dateStr) {
    if (!dateStr) {
        const dateInput = document.getElementById('date');
        dateStr = dateInput ? dateInput.value : '';
    }

    if (dateStr) {
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const year = parseInt(parts[2]) + 543;
            const displayDate = `${day}${month}${year}`;
            const spacesCount = parseInt(localStorage.getItem('chequeDateSpacing') || '3');
            const spacer = ' '.repeat(isNaN(spacesCount) ? 3 : spacesCount);
            const spacedDate = displayDate.split('').join(spacer);
            const dd = document.getElementById('dateDisplay');
            if (dd) {
                dd.textContent = spacedDate;
                dd.style.whiteSpace = 'pre';
            }
        }
    }
}

// Date spacing for both tabs
function saveDateSpacing(val) {
    const n = parseInt(val);
    localStorage.setItem('chequeDateSpacing', isNaN(n) ? '3' : String(n));
}

function updateDateSpacingLabel(val) {
    const el = document.getElementById('date_spacing_value');
    if (el) el.textContent = String(val);
}

function saveDateSpacingDesigner(val) {
    localStorage.setItem('chequeDateSpacing', val);
}

function updateDateSpacingDesigner(val) {
    const label = document.getElementById('date_spacing_value_designer');
    if (label) label.textContent = val;

    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1;
    const year = today.getFullYear() + 543;

    const spacer = ' '.repeat(parseInt(val));
    const dateString = `${day}${month}${year}`;
    const spacedDate = dateString.split('').join(spacer);
    const dd = document.getElementById('dateDisplay');
    if (dd) {
        dd.textContent = spacedDate;
        dd.style.whiteSpace = 'pre';
    }
}

function loadDateSpacing() {
    const saved = parseInt(localStorage.getItem('chequeDateSpacing') || '3');

    // Load for print tab
    const spacingInput = document.getElementById('date_spacing');
    if (spacingInput) {
        spacingInput.value = isNaN(saved) ? 3 : saved;
        updateDateSpacingLabel(spacingInput.value);
    }

    // Load for designer tab
    const designerInput = document.getElementById('date_spacing_designer');
    const designerLabel = document.getElementById('date_spacing_value_designer');
    if (designerInput) designerInput.value = saved;
    if (designerLabel) designerLabel.textContent = saved;

    // Load print offsets
    const off = getPrintOffsets();
    const ox = document.getElementById('print_offset_x');
    const oy = document.getElementById('print_offset_y');
    if (ox) ox.value = off.x;
    if (oy) oy.value = off.y;
}

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

document.getElementById('payee')?.addEventListener('input', updatePayeeDisplay);
document.getElementById('amount')?.addEventListener('input', updateAmountText);

function updatePayeeDisplay() {
    const payeeInput = document.getElementById('payee');
    const payeeDisplay = document.getElementById('payeeDisplay');
    if (payeeInput && payeeDisplay) {
        payeeDisplay.textContent = payeeInput.value || '<สั่งจ่าย>';
    }
}

function updateAmountText() {
    const amountInput = document.getElementById('amount');
    const amount = amountInput ? amountInput.value : '';
    const parsed = parseFloat(amount.replace(/,/g, ''));

    const amountTextEl = document.getElementById('amount-text');
    const amountTextDisplay = document.getElementById('amountText');
    const amountNumberDisplay = document.getElementById('amountNumber');

    if (!isNaN(parsed) && parsed > 0) {
        const text = thaiNumberToText(parsed);
        if (amountTextEl) amountTextEl.textContent = text;
        if (amountTextDisplay) amountTextDisplay.textContent = text;

        const formatted = parsed.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (amountNumberDisplay) amountNumberDisplay.textContent = `***${formatted}***`;
    } else {
        if (amountTextEl) amountTextEl.textContent = '';
        if (amountTextDisplay) amountTextDisplay.textContent = '';
        if (amountNumberDisplay) amountNumberDisplay.textContent = '***0.00***';
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

    if (baht > 0) {
        result = convertIntegerToThai(baht, ones, places) + 'บาท';
    }

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

        if (position === 5) {
            result += ones[digit] + 'แสน';
        } else if (position === 4) {
            result += ones[digit] + 'หมื่น';
        } else if (position === 3) {
            result += ones[digit] + 'พัน';
        } else if (position === 2) {
            result += ones[digit] + 'ร้อย';
        } else if (position === 1) {
            if (digit === 1) {
                result += 'สิบ';
            } else if (digit === 2) {
                result += 'ยี่สิบ';
            } else {
                result += ones[digit] + 'สิบ';
            }
        } else {
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
    if (checkbox && element) {
        element.style.display = checkbox.checked ? 'block' : 'none';
    }
}

function toggleLine() {
    const checkbox = document.getElementById('show_line');
    const element = document.getElementById('lineHolder');
    if (checkbox && element) {
        element.style.display = checkbox.checked ? 'block' : 'none';
    }
}

// =====================================
// Cheque Operations (Print Tab)
// =====================================
async function useNextChequeNo() {
    const branchSelect = document.getElementById('branch_code');
    const branch = branchSelect ? branchSelect.value : '';

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
        const chequeNumInput = document.getElementById('cheque_number');
        if (data.cheque_number && chequeNumInput) {
            chequeNumInput.value = data.cheque_number;
            saveFormData();
        }
    } catch (error) {
        console.error('Error fetching next cheque number:', error);
    }
}

async function loadChequeByNumber() {
    const chequeNumInput = document.getElementById('cheque_number');
    const chequeNumber = chequeNumInput ? chequeNumInput.value.trim() : '';

    if (!chequeNumber) {
        isExistingCheque = false;
        loadedChequeId = null;
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/cheques/number/${encodeURIComponent(chequeNumber)}`);
        const result = await response.json();

        if (response.status === 404 || !result.success) {
            console.log('Cheque number not found - will create new cheque');
            isExistingCheque = false;
            loadedChequeId = null;
            return;
        }

        if (!response.ok) {
            console.error('Error loading cheque:', result);
            return;
        }

        if (result.success && result.data) {
            const cheque = result.data;

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
                isExistingCheque = true;
                loadedChequeId = cheque.id;

                if (cheque.branch_code) {
                    const branchSelect = document.getElementById('branch_code');
                    if (branchSelect) branchSelect.value = cheque.branch_code;
                }
                if (cheque.bank) {
                    const bankSelect = document.getElementById('bank_code');
                    if (bankSelect) bankSelect.value = cheque.bank;
                    await loadBankTemplate();
                }

                if (cheque.date) {
                    const dateParts = cheque.date.split('-');
                    if (dateParts.length === 3) {
                        const [year, month, day] = dateParts;
                        const displayDate = `${parseInt(day)}/${parseInt(month)}/${year}`;
                        const dateInput = document.getElementById('date');
                        if (dateInput) dateInput.value = displayDate;
                        updateDateDisplay(displayDate);
                    }
                }

                const payeeInput = document.getElementById('payee');
                const amountInput = document.getElementById('amount');
                if (payeeInput) payeeInput.value = cheque.payee || '';
                if (amountInput) amountInput.value = parseFloat(cheque.amount).toFixed(2);

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
    }
}

async function printCheque() {
    console.log('=== printCheque() called ===');

    const branchSelect = document.getElementById('branch_code');
    const bankSelect = document.getElementById('bank_code');
    const chequeNumInput = document.getElementById('cheque_number');
    const dateInput = document.getElementById('date');
    const payeeInput = document.getElementById('payee');
    const amountInput = document.getElementById('amount');

    const branch = branchSelect ? branchSelect.value : '';
    const bank = bankSelect ? bankSelect.value : '';
    const chequeNum = chequeNumInput ? chequeNumInput.value : '';
    const dateVal = dateInput ? dateInput.value : '';
    const payee = payeeInput ? payeeInput.value : '';
    const amount = amountInput ? amountInput.value : '';

    console.log('Form data:', { branch, bank, chequeNum, dateVal, payee, amount });

    if (!chequeNum || !dateVal || !payee || !amount) {
        console.warn('Validation failed: Missing required fields');
        Swal.fire({
            title: 'ข้อมูลไม่ครบถ้วน!',
            text: 'กรุณากรอกข้อมูลให้ครบทุกช่อง',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

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
    let formattedDate = dateVal;
    if (dateVal.includes('/')) {
        const parts = dateVal.split('/');
        if (parts.length === 3) {
            const [day, month, year] = parts;
            formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }
    }

    console.log('Date conversion:', dateVal, '=>', formattedDate);

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

            // Auto-clear after print if enabled
            const autoClearCheckbox = document.getElementById('auto_clear_after_print');
            if (autoClearCheckbox && autoClearCheckbox.checked) {
                setTimeout(() => {
                    clearFormAfterPrint();
                }, 1000); // Wait 1 second after opening print dialog
            }
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

            if (response.status === 422) {
                console.error('Validation errors:', data.errors);

                if (data.message && (data.message.includes('already exists') || data.message.includes('already been taken'))) {
                    console.log('Duplicate cheque number detected, attempting to load existing data...');

                    try {
                        const loadResponse = await fetch(`${API_BASE}/cheques/number/${encodeURIComponent(chequeNum)}`);
                        const loadResult = await loadResponse.json();

                        if (loadResult.success && loadResult.data) {
                            const existingCheque = loadResult.data;
                            console.log('Loaded existing cheque:', existingCheque);

                            isExistingCheque = true;
                            loadedChequeId = existingCheque.id;

                            if (existingCheque.branch_code && branchSelect) {
                                branchSelect.value = existingCheque.branch_code;
                            }
                            if (existingCheque.bank && bankSelect) {
                                bankSelect.value = existingCheque.bank;
                                await loadBankTemplate();
                            }

                            if (existingCheque.date) {
                                const dateObj = new Date(existingCheque.date);
                                const day = dateObj.getDate();
                                const month = dateObj.getMonth() + 1;
                                const year = dateObj.getFullYear();
                                const displayDate = `${day}/${month}/${year}`;
                                if (dateInput) dateInput.value = displayDate;
                            }

                            if (payeeInput) payeeInput.value = existingCheque.payee || '';
                            if (amountInput) amountInput.value = parseFloat(existingCheque.amount).toFixed(2);

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

                                setTimeout(() => {
                                    console.log('Opening print window for duplicate cheque...');
                                    forcePrint();
                                }, 500);
                            } else {
                                console.log('User cancelled printing, resetting flags');
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

        isExistingCheque = true;
        loadedChequeId = data.id;

        Swal.fire({
            title: 'บันทึกสำเร็จ!',
            text: 'ข้อมูลเช็คถูกบันทึกแล้ว กำลังเปิดหน้าพิมพ์...',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });

        setTimeout(() => {
            console.log('Opening print window...');
            forcePrint();

            // Auto-clear after print if enabled
            const autoClearCheckbox = document.getElementById('auto_clear_after_print');
            if (autoClearCheckbox && autoClearCheckbox.checked) {
                setTimeout(() => {
                    clearFormAfterPrint();
                }, 1000); // Wait 1 second after opening print dialog
            }
        }, 500);

    } catch (error) {
        console.error('✗ Error saving cheque:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack
        });

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

            // Auto-clear after print if enabled (even when save failed)
            const autoClearCheckbox = document.getElementById('auto_clear_after_print');
            if (autoClearCheckbox && autoClearCheckbox.checked) {
                setTimeout(() => {
                    clearFormAfterPrint();
                }, 1000); // Wait 1 second after opening print dialog
            }
        } else {
            console.log('User cancelled printing');
        }
    }
}

function forcePrint() {
    console.log('=== Force Print with Minimal CSS ===');

    const printWindow = window.open('', '_blank', 'width=900,height=500');

    const chequePreview = document.getElementById('chequePreview');
    if (!chequePreview) {
        console.error('Cheque preview not found!');
        return;
    }

    const clonedCheque = chequePreview.cloneNode(true);

    // Apply user-defined printer offsets (px)
    try {
        const off = getPrintOffsets();
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
                    width: 890px;
                    height: 445px;
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
                    padding: 0;
                    margin: 0;
                    line-height: 1;
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

async function clearForm() {
    const result = await Swal.fire({
        title: 'ยืนยันการล้างข้อมูล',
        text: 'จะล้างเฉพาะ วันที่, จ่ายให้, และจำนวนเงิน',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ล้างข้อมูล',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        clearFormFields();

        Swal.fire({
            title: 'ล้างข้อมูลสำเร็จ!',
            text: 'พร้อมสำหรับการกรอกข้อมูลใหม่',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    }
}

// ล้างเฉพาะฟิลด์ที่จำเป็น (ไม่ล้าง สาขา, ธนาคาร, เลขเช็ค)
function clearFormFields() {
    console.log('Clearing form fields...');

    isExistingCheque = false;
    loadedChequeId = null;

    // ล้างวันที่ (ตั้งเป็นวันปัจจุบัน)
    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1;
    const year = today.getFullYear();
    const dateStr = `${day}/${month}/${year}`;
    const dateInput = document.getElementById('date');
    if (dateInput) {
        dateInput.value = dateStr;
        // Update flatpickr instance
        if (dateInput._flatpickr) {
            dateInput._flatpickr.setDate(today);
        }
    }
    updateDateDisplay(dateStr);

    // ล้างจ่ายให้
    const payeeInput = document.getElementById('payee');
    if (payeeInput) payeeInput.value = '';

    // ล้างจำนวนเงิน
    const amountInput = document.getElementById('amount');
    if (amountInput) amountInput.value = '';

    // Update displays
    const payeeDisplay = document.getElementById('payeeDisplay');
    const amountTextDisplay = document.getElementById('amountText');
    const amountNumberDisplay = document.getElementById('amountNumber');
    const amountTextEl = document.getElementById('amount-text');

    if (payeeDisplay) payeeDisplay.textContent = '<สั่งจ่าย>';
    if (amountTextDisplay) amountTextDisplay.textContent = '';
    if (amountNumberDisplay) amountNumberDisplay.textContent = '***0.00***';
    if (amountTextEl) amountTextEl.textContent = '';

    // Update localStorage (keep branch, bank, cheque_number)
    saveFormData();
}

// Auto-clear after print
function clearFormAfterPrint() {
    console.log('Auto-clearing form after print...');

    // ล้างเฉพาะ จ่ายให้ และ จำนวนเงิน (ไม่ล้างวันที่)
    const payeeInput = document.getElementById('payee');
    if (payeeInput) payeeInput.value = '';

    const amountInput = document.getElementById('amount');
    if (amountInput) amountInput.value = '';

    // Update displays
    const payeeDisplay = document.getElementById('payeeDisplay');
    const amountTextDisplay = document.getElementById('amountText');
    const amountNumberDisplay = document.getElementById('amountNumber');
    const amountTextEl = document.getElementById('amount-text');

    if (payeeDisplay) payeeDisplay.textContent = '<สั่งจ่าย>';
    if (amountTextDisplay) amountTextDisplay.textContent = '';
    if (amountNumberDisplay) amountNumberDisplay.textContent = '***0.00***';
    if (amountTextEl) amountTextEl.textContent = '';

    // เพิ่มเลขที่เช็ค +1 (รองรับทุก format)
    incrementChequeNumber();

    isExistingCheque = false;
    loadedChequeId = null;

    saveFormData();

    // Auto-focus ที่ฟิลด์ "จ่ายให้"
    if (payeeInput) {
        setTimeout(() => {
            payeeInput.focus();
            console.log('Auto-focused on payee input');
        }, 100);
    }
}

// Increment cheque number (support any format)
function incrementChequeNumber() {
    const chequeNumInput = document.getElementById('cheque_number');
    if (!chequeNumInput) return;

    const currentValue = chequeNumInput.value.trim();
    if (!currentValue) return;

    // Find the last number in the string
    const matches = currentValue.match(/(\d+)(?!.*\d)/);

    if (matches) {
        const lastNumber = matches[1];
        const numberLength = lastNumber.length;
        const incrementedNumber = (parseInt(lastNumber) + 1).toString();

        // Pad with zeros to maintain original length
        const paddedNumber = incrementedNumber.padStart(numberLength, '0');

        // Replace the last number in the original string
        const newValue = currentValue.replace(/(\d+)(?!.*\d)/, paddedNumber);

        chequeNumInput.value = newValue;
        console.log(`Cheque number incremented: ${currentValue} → ${newValue}`);
    } else {
        console.warn('No number found in cheque number:', currentValue);
    }
}

// Save auto-clear setting
function saveAutoClearSetting() {
    const checkbox = document.getElementById('auto_clear_after_print');
    if (checkbox) {
        localStorage.setItem('autoClearAfterPrint', checkbox.checked ? '1' : '0');
        console.log('Auto-clear setting saved:', checkbox.checked);
    }
}

// Load auto-clear setting
function loadAutoClearSetting() {
    const checkbox = document.getElementById('auto_clear_after_print');
    const saved = localStorage.getItem('autoClearAfterPrint');
    if (checkbox && saved) {
        checkbox.checked = saved === '1';
    }
}

// =====================================
// Form Data Persistence
// =====================================
function saveFormData() {
    const branchSelect = document.getElementById('branch_code');
    const bankSelect = document.getElementById('bank_code');
    const chequeNumInput = document.getElementById('cheque_number');
    const dateInput = document.getElementById('date');
    const payeeInput = document.getElementById('payee');
    const amountInput = document.getElementById('amount');
    const acPayeeCheck = document.getElementById('show_ac_payee');
    const lineCheck = document.getElementById('show_line');

    const formData = {
        branch_code: branchSelect ? branchSelect.value : '',
        bank_code: bankSelect ? bankSelect.value : '',
        cheque_number: chequeNumInput ? chequeNumInput.value : '',
        date: dateInput ? dateInput.value : '',
        payee: payeeInput ? payeeInput.value : '',
        amount: amountInput ? amountInput.value : '',
        show_ac_payee: acPayeeCheck ? acPayeeCheck.checked : true,
        show_line: lineCheck ? lineCheck.checked : true
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

        if (id === 'cheque_number') {
            element.addEventListener('input', function() {
                isExistingCheque = false;
                loadedChequeId = null;
            });
        }
    }
});

// =====================================
// Payee Autocomplete
// =====================================
let autocompleteTimeout;

function setupPayeeAutocomplete() {
    const payeeInput = document.getElementById('payee');
    const autocompleteDiv = document.getElementById('payee-autocomplete');

    if (!payeeInput || !autocompleteDiv) {
        console.warn('Payee autocomplete elements not found');
        return;
    }

    console.log('✓ Payee autocomplete initialized');

    payeeInput.addEventListener('input', function() {
        clearTimeout(autocompleteTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            autocompleteDiv.classList.add('hidden');
            return;
        }

        autocompleteTimeout = setTimeout(async () => {
            try {
                const branchSelect = document.getElementById('branch_code');
                const branch = branchSelect ? branchSelect.value : '';
                const url = `${API_BASE}/payees?q=${encodeURIComponent(query)}&limit=10${branch ? '&branch=' + branch : ''}`;

                console.log('Fetching payees:', url);
                const response = await fetch(url);
                const payees = await response.json();

                console.log('Found payees:', payees.length);

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

    payeeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            autocompleteDiv.classList.add('hidden');
        }
    });

    // Close autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!payeeInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
            autocompleteDiv.classList.add('hidden');
        }
    });
}

function selectPayee(name) {
    const payeeInput = document.getElementById('payee');
    const autocompleteDiv = document.getElementById('payee-autocomplete');

    if (payeeInput) payeeInput.value = name;
    if (autocompleteDiv) autocompleteDiv.classList.add('hidden');
    updatePayeeDisplay();
    saveFormData();
}

// =====================================
// Designer Tab Functions
// =====================================
async function saveAsTemplate() {
    const bankTemplate = document.getElementById('bank_template');
    const bank = bankTemplate ? bankTemplate.value : '';

    if (bank === 'custom') {
        Swal.fire({
            title: 'ไม่สามารถบันทึกได้!',
            text: 'กรุณาเลือกธนาคารก่อนบันทึก Template',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

    const positions = collectPositions();

    try {
        const response = await fetch(`${API_BASE}/templates`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ bank, template_json: positions })
        });

        if (response.ok) {
            Swal.fire({
                title: 'บันทึกสำเร็จ!',
                text: 'Template ถูกบันทึกสำหรับธนาคารนี้แล้ว',
                icon: 'success',
                confirmButtonColor: '#10b981'
            });
        } else {
            throw new Error('Save failed');
        }
    } catch (error) {
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่สามารถบันทึก Template ได้',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
}

function resetPositions() {
    Swal.fire({
        title: 'รีเซ็ตตำแหน่ง?',
        text: 'ตำแหน่งทั้งหมดจะถูกรีเซ็ตเป็นค่าเริ่มต้น',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ใช่, รีเซ็ต',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            applyPositions(defaultPositions);
            savePositions();
            Swal.fire({
                title: 'รีเซ็ตแล้ว!',
                text: 'ตำแหน่งถูกรีเซ็ตเป็นค่าเริ่มต้นแล้ว',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

function testPrintCheque() {
    const preview = document.getElementById('chequePreview');
    if (!preview) {
        alert('ไม่พบ Preview!');
        return;
    }

    const clonedCheque = preview.cloneNode(true);

    // Get current dimensions
    const currentWidth = preview.style.width || '890px';
    const currentHeight = preview.style.height || '445px';

    // Remove background image for print
    clonedCheque.style.backgroundImage = 'none';
    clonedCheque.classList.remove('has-background');

    // Add watermark to cloned cheque
    const watermark = document.createElement('div');
    watermark.className = 'test-watermark';
    watermark.textContent = 'ทดสอบพิมพ์';
    clonedCheque.appendChild(watermark);

    // Create print window
    const printWindow = window.open('', '_blank', 'width=900,height=500');

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>ทดสอบพิมพ์เช็ค</title>
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
                }

                .cheque-preview {
                    position: relative;
                    width: ${currentWidth};
                    height: ${currentHeight};
                    border: none;
                    background: white;
                    margin: 0;
                }

                .draggable {
                    position: absolute;
                    white-space: nowrap;
                    border: none;
                    background: transparent;
                    padding: 0;
                    margin: 0;
                    line-height: 1;
                }

                .ac-payee {
                    font-weight: bold !important;
                    font-size: 18px !important;
                    transform: rotate(-40deg);
                    transform-origin: left top;
                    color: #ff0000 !important;
                    text-decoration: overline underline !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                #dateDisplay { white-space: pre; }

                .line-holder {
                    font-size: 20px;
                    font-weight: bold;
                }

                /* Watermark */
                .test-watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 72px;
                    font-weight: bold;
                    color: rgba(200, 200, 200, 0.3);
                    pointer-events: none;
                    user-select: none;
                    z-index: 999;
                    white-space: nowrap;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                @media print {
                    .test-watermark {
                        color: rgba(180, 180, 180, 0.25) !important;
                    }
                }
            </style>
        </head>
        <body>
            ${clonedCheque.outerHTML}
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();

    console.log('Test print window opened');
}
</script>
@endpush
