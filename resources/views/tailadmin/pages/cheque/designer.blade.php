@extends('tailadmin.layouts.app')

@section('title', 'ออกแบบ & ปรับแต่งเช็ค - ' . config('app.name'))

@push('styles')
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
    box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.3);
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

.property-panel {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}

.dark .property-panel {
    background: #1f2937;
    border-color: #374151;
}

.control-group {
    margin-bottom: 12px;
}

.control-group label {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            🎨 ออกแบบ & ปรับแต่งเช็ค
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('cheque.print') }}">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">ออกแบบ</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Left Sidebar: Controls (1 column) -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⚙️ การตั้งค่า</h3>

                <!-- Bank Template -->
                <div class="property-panel">
                    <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">📋 เทมเพลตธนาคาร</h4>
                    <select id="bank_template" onchange="loadTemplate()" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                        <option value="custom">กำหนดเอง</option>
                        <option value="scb">ธนาคารไทยพาณิชย์ (SCB)</option>
                        <option value="kbank">ธนาคารกสิกรไทย (KBANK)</option>
                        <option value="ktb">ธนาคารกรุงไทย (KTB)</option>
                    </select>
                </div>

                <!-- Paper Size -->
                <div class="property-panel">
                    <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">📐 ขนาดกระดาษ</h4>
                    <select id="paper_size" onchange="changePaperSize()" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white mb-3">
                        <option value="800x350">มาตรฐาน (800x350px)</option>
                        <option value="750x320">เล็ก (750x320px)</option>
                        <option value="850x380">ใหญ่ (850x380px)</option>
                        <option value="custom">กำหนดเอง</option>
                    </select>

                    <div id="custom_size_panel" style="display:none;">
                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <div>
                                <label class="text-sm">กว้าง (px)</label>
                                <input type="number" id="custom_width" value="800" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                            </div>
                            <div>
                                <label class="text-sm">สูง (px)</label>
                                <input type="number" id="custom_height" value="350" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                            </div>
                        </div>
                        <button onclick="applyCustomSize()" class="w-full rounded bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                            ✓ ใช้ขนาดนี้
                        </button>
                    </div>
                </div>

                <!-- Element Properties -->
                <div class="property-panel" id="element_properties" style="display:none;">
                    <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">✏️ แก้ไข: <span id="selected_element_name">-</span></h4>

                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <label class="text-sm">ตำแหน่ง X</label>
                            <input type="number" id="prop_x" onchange="updateElementProperty()" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                        </div>
                        <div>
                            <label class="text-sm">ตำแหน่ง Y</label>
                            <input type="number" id="prop_y" onchange="updateElementProperty()" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-sm">ขนาดตัวอักษร (px)</label>
                        <input type="number" id="prop_font_size" onchange="updateElementProperty()" min="10" max="50" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="text-sm">สี</label>
                        <input type="color" id="prop_color" onchange="updateElementProperty()" class="w-full h-10 rounded border border-gray-300 dark:border-gray-700">
                    </div>

                    <div class="mb-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="prop_bold" onchange="updateElementProperty()" class="rounded border-gray-300">
                            <span class="text-sm">ตัวหนา</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-2">
                    <button onclick="saveAsTemplate()" class="w-full rounded bg-green-500 px-4 py-2.5 text-white hover:bg-green-600">
                        💾 บันทึก Template
                    </button>
                    <button onclick="resetPositions()" class="w-full rounded bg-red-500 px-4 py-2.5 text-white hover:bg-red-600">
                        🔄 รีเซ็ตตำแหน่ง
                    </button>
                    <a href="{{ route('cheque.print') }}" class="block w-full rounded bg-gray-100 px-4 py-2.5 text-center text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        ← กลับหน้าพิมพ์เช็ค
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Preview (3 columns) -->
        <div class="lg:col-span-3">
            <div class="cheque-workspace">
                <div class="mb-4 space-y-2">
                    <div class="flex gap-2 flex-wrap">
                        <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                            💡 คลิกที่องค์ประกอบเพื่อแก้ไข
                        </span>
                        <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                            🖱️ ลากเพื่อย้ายตำแหน่ง
                        </span>
                        <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm">
                            ⌨️ ใช้ตัวเลขเพื่อปรับละเอียด
                        </span>
                    </div>
                </div>

                <div class="cheque-preview" id="cheque_preview">
                    <div class="draggable ac-payee" id="acPayee" data-name="A/C PAYEE ONLY">
                        A/C PAYEE ONLY
                    </div>
                    <div class="draggable line-holder" id="lineHolder" data-name="เส้น (หรือผู้ถือ)">
                        --------
                    </div>
                    <div class="draggable" id="dateDisplay" data-name="วันที่">
                        25/10/2568
                    </div>
                    <div class="draggable" id="payeeDisplay" data-name="ชื่อผู้รับเงิน">
                        บริษัท ตัวอย่าง จำกัด
                    </div>
                    <div class="draggable" id="amountText" data-name="จำนวนเงิน (ตัวอักษร)">
                        หนึ่งพันสองร้อยห้าสิบบาทถ้วน
                    </div>
                    <div class="draggable" id="amountNumber" data-name="จำนวนเงิน (ตัวเลข)">
                        ***1,250.00***
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const API_BASE = '/api';

// Element selection and dragging
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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadPositions();
    setupDragAndDrop();
});

// Load positions
function loadPositions() {
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

// Load template
async function loadTemplate() {
    const bank = document.getElementById('bank_template').value;
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
            selectElement(element);

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

            // Update property panel
            if (selectedElement === draggedElement) {
                document.getElementById('prop_x').value = Math.round(x);
                document.getElementById('prop_y').value = Math.round(y);
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

// Select element for editing
function selectElement(element) {
    selectedElement = element;
    document.getElementById('element_properties').style.display = 'block';
    document.getElementById('selected_element_name').textContent = element.getAttribute('data-name');

    // Populate property panel
    const computedStyle = window.getComputedStyle(element);
    document.getElementById('prop_x').value = parseInt(element.style.left) || 0;
    document.getElementById('prop_y').value = parseInt(element.style.top) || 0;
    document.getElementById('prop_font_size').value = parseInt(computedStyle.fontSize);
    document.getElementById('prop_color').value = rgbToHex(computedStyle.color);
    document.getElementById('prop_bold').checked = computedStyle.fontWeight === 'bold' || computedStyle.fontWeight >= 700;
}

// Update element property
function updateElementProperty() {
    if (!selectedElement) return;

    const x = parseInt(document.getElementById('prop_x').value);
    const y = parseInt(document.getElementById('prop_y').value);
    const fontSize = parseInt(document.getElementById('prop_font_size').value);
    const color = document.getElementById('prop_color').value;
    const bold = document.getElementById('prop_bold').checked;

    selectedElement.style.left = x + 'px';
    selectedElement.style.top = y + 'px';
    selectedElement.style.right = 'auto';
    selectedElement.style.fontSize = fontSize + 'px';
    selectedElement.style.color = color;
    selectedElement.style.fontWeight = bold ? 'bold' : 'normal';

    savePositions();
}

// RGB to Hex
function rgbToHex(rgb) {
    const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!match) return '#000000';
    return '#' + [match[1], match[2], match[3]].map(x => {
        const hex = parseInt(x).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    }).join('');
}

// Save positions
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

// Save as template
async function saveAsTemplate() {
    const bank = document.getElementById('bank_template').value;
    if (bank === 'custom') {
        Swal.fire({
            title: 'ไม่สามารถบันทึกได้!',
            text: 'กรุณาเลือกธนาคารก่อนบันทึก Template',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
        return;
    }

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

    try {
        const response = await fetch(`${API_BASE}/templates`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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

// Reset positions
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

// Change paper size
function changePaperSize() {
    const size = document.getElementById('paper_size').value;
    const preview = document.getElementById('cheque_preview');

    if (size === 'custom') {
        document.getElementById('custom_size_panel').style.display = 'block';
    } else {
        document.getElementById('custom_size_panel').style.display = 'none';
        const [width, height] = size.split('x');
        preview.style.width = width + 'px';
        preview.style.height = height + 'px';
        localStorage.setItem('paperSize', size);
    }
}

// Apply custom size
function applyCustomSize() {
    const width = document.getElementById('custom_width').value;
    const height = document.getElementById('custom_height').value;
    const preview = document.getElementById('cheque_preview');
    preview.style.width = width + 'px';
    preview.style.height = height + 'px';
    localStorage.setItem('paperSize', `${width}x${height}`);
}
</script>
@endpush
