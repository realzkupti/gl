@extends('tailadmin.layouts.app')

@section('title', 'ตั้งค่าระบบ - ระบบเช็ค')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">ตั้งค่าระบบเช็ค</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">Dashboard /</a></li>
                <li><a href="{{ route('cheque.print') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">ตั้งค่า</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <!-- General Settings -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">ตั้งค่าทั่วไป</h3>

            <form id="general-settings" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">บริษัท/องค์กร</label>
                        <input
                            type="text"
                            id="company-name"
                            value="บริษัท ตัวอย่าง จำกัด"
                            class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">รูปแบบวันที่</label>
                        <select id="date-format" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="th">พ.ศ. (ไทย)</option>
                            <option value="en">ค.ศ. (สากล)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">สกุลเงิน</label>
                        <select id="currency" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="THB">บาท (THB)</option>
                            <option value="USD">ดอลลาร์ (USD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ภาษาเช็ค</label>
                        <select id="cheque-language" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="th">ภาษาไทย</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="auto-backup"
                        checked
                        class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                    />
                    <label for="auto-backup" class="text-sm font-medium">สำรองข้อมูลอัตโนมัติทุกวัน</label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="saveGeneralSettings()" class="rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                        บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>

        <!-- Cheque Numbering -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">รูปแบบเลขที่เช็ค</h3>

            <form id="numbering-settings" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">รูปแบบเลขที่</label>
                        <select id="number-format" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700" onchange="updateNumberPreview()">
                            <option value="simple">ตัวเลขเรียง (000001)</option>
                            <option value="prefix-date">Prefix + วันที่ + เลข (CHQ-20250101-001)</option>
                            <option value="branch-date">สาขา + วันที่ + เลข (HQ-20250101-001)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Prefix (คำนำหน้า)</label>
                        <input
                            type="text"
                            id="number-prefix"
                            value="CHQ"
                            maxlength="10"
                            onkeyup="updateNumberPreview()"
                            class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700"
                        />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">จำนวนหลัก (Padding)</label>
                        <select id="number-padding" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700" onchange="updateNumberPreview()">
                            <option value="4">4 หลัก (0001)</option>
                            <option value="5">5 หลัก (00001)</option>
                            <option value="6" selected>6 หลัก (000001)</option>
                            <option value="7">7 หลัก (0000001)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ตัวอย่างเลขที่เช็ค</label>
                        <div class="flex h-11 items-center rounded border border-gray-300 bg-gray-50 px-4 font-mono text-sm dark:border-gray-700 dark:bg-gray-800" id="number-preview">
                            000001
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/50 dark:bg-yellow-900/20">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">คำเตือน</p>
                            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                การเปลี่ยนรูปแบบเลขที่จะมีผลกับเช็คใบใหม่เท่านั้น เช็คที่สร้างไว้แล้วจะไม่เปลี่ยนแปลง
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="saveNumberingSettings()" class="rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                        บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>

        <!-- Print Settings -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">ตั้งค่าการพิมพ์</h3>

            <form id="print-settings" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">ขนาดกระดาษ</label>
                        <select id="paper-size" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="a4">A4</option>
                            <option value="letter">Letter</option>
                            <option value="cheque" selected>ขนาดเช็คมาตรฐาน</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ทิศทางกระดาษ</label>
                        <select id="paper-orientation" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="portrait">แนวตั้ง (Portrait)</option>
                            <option value="landscape" selected>แนวนอน (Landscape)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ฟอนต์พิมพ์</label>
                        <select id="print-font" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="sarabun" selected>TH Sarabun New</option>
                            <option value="angsana">Angsana New</option>
                            <option value="cordia">Cordia New</option>
                            <option value="arial">Arial</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ขนาดฟอนต์</label>
                        <select id="print-font-size" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                            <option value="12">12pt</option>
                            <option value="14" selected>14pt</option>
                            <option value="16">16pt</option>
                            <option value="18">18pt</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="print-auto"
                            class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        />
                        <label for="print-auto" class="text-sm font-medium">เปิดหน้าต่างพิมพ์อัตโนมัติหลังบันทึก</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="print-border"
                            checked
                            class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        />
                        <label for="print-border" class="text-sm font-medium">แสดงกรอบเช็คในการพิมพ์</label>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="savePrintSettings()" class="rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                        บันทึกการตั้งค่า
                    </button>
                    <button type="button" onclick="testPrint()" class="rounded border border-gray-300 px-6 py-2.5 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                        ทดสอบพิมพ์
                    </button>
                </div>
            </form>
        </div>

        <!-- Backup & Reset -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-white">สำรองข้อมูลและรีเซ็ต</h3>

            <div class="space-y-4">
                <div class="flex items-start justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">สำรองข้อมูล</h4>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            ดาวน์โหลดข้อมูลทั้งหมดในรูปแบบ JSON
                        </p>
                    </div>
                    <button onclick="backupData()" class="rounded bg-blue-500 px-4 py-2 text-sm text-white hover:bg-blue-600">
                        สำรองข้อมูล
                    </button>
                </div>

                <div class="flex items-start justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">นำเข้าข้อมูล</h4>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            นำเข้าข้อมูลจากไฟล์สำรอง
                        </p>
                    </div>
                    <button onclick="document.getElementById('restore-file').click()" class="rounded bg-green-500 px-4 py-2 text-sm text-white hover:bg-green-600">
                        นำเข้าข้อมูล
                    </button>
                    <input type="file" id="restore-file" accept=".json" style="display: none" onchange="restoreData(event)" />
                </div>

                <div class="flex items-start justify-between rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-900/20">
                    <div>
                        <h4 class="font-medium text-red-900 dark:text-red-200">รีเซ็ตระบบ</h4>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                            ลบข้อมูลทั้งหมดและรีเซ็ตค่าเริ่มต้น (ไม่สามารถย้อนกลับได้)
                        </p>
                    </div>
                    <button onclick="resetSystem()" class="rounded bg-red-500 px-4 py-2 text-sm text-white hover:bg-red-600">
                        รีเซ็ตระบบ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update number preview
function updateNumberPreview() {
    const format = document.getElementById('number-format').value;
    const prefix = document.getElementById('number-prefix').value;
    const padding = parseInt(document.getElementById('number-padding').value);
    const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    const number = '1'.padStart(padding, '0');

    let preview = '';
    switch (format) {
        case 'simple':
            preview = number;
            break;
        case 'prefix-date':
            preview = `${prefix}-${today}-${number}`;
            break;
        case 'branch-date':
            preview = `HQ-${today}-${number}`;
            break;
    }

    document.getElementById('number-preview').textContent = preview;
}

// Save general settings
function saveGeneralSettings() {
    const settings = {
        company_name: document.getElementById('company-name').value,
        date_format: document.getElementById('date-format').value,
        currency: document.getElementById('currency').value,
        cheque_language: document.getElementById('cheque-language').value,
        auto_backup: document.getElementById('auto-backup').checked
    };

    localStorage.setItem('cheque_general_settings', JSON.stringify(settings));
    alert('บันทึกการตั้งค่าทั่วไปเรียบร้อย');
}

// Save numbering settings
function saveNumberingSettings() {
    const settings = {
        format: document.getElementById('number-format').value,
        prefix: document.getElementById('number-prefix').value,
        padding: document.getElementById('number-padding').value
    };

    localStorage.setItem('cheque_numbering_settings', JSON.stringify(settings));
    alert('บันทึกการตั้งค่าเลขที่เช็คเรียบร้อย');
}

// Save print settings
function savePrintSettings() {
    const settings = {
        paper_size: document.getElementById('paper-size').value,
        orientation: document.getElementById('paper-orientation').value,
        font: document.getElementById('print-font').value,
        font_size: document.getElementById('print-font-size').value,
        auto_print: document.getElementById('print-auto').checked,
        show_border: document.getElementById('print-border').checked
    };

    localStorage.setItem('cheque_print_settings', JSON.stringify(settings));
    alert('บันทึกการตั้งค่าการพิมพ์เรียบร้อย');
}

// Test print
function testPrint() {
    alert('ฟีเจอร์ทดสอบพิมพ์จะเปิดหน้าต่างพิมพ์ตัวอย่าง');
    window.print();
}

// Backup data
async function backupData() {
    try {
        const cheques = await axios.get('/api/cheques');
        const branches = await axios.get('/api/branches');

        const backup = {
            date: new Date().toISOString(),
            cheques: cheques.data,
            branches: branches.data,
            settings: {
                general: JSON.parse(localStorage.getItem('cheque_general_settings') || '{}'),
                numbering: JSON.parse(localStorage.getItem('cheque_numbering_settings') || '{}'),
                print: JSON.parse(localStorage.getItem('cheque_print_settings') || '{}'),
                template: JSON.parse(localStorage.getItem('cheque_template') || '{}')
            }
        };

        const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `cheque-backup-${new Date().toISOString().slice(0, 10)}.json`;
        a.click();
        URL.revokeObjectURL(url);

        alert('สำรองข้อมูลเรียบร้อย');
    } catch (error) {
        console.error('Backup failed:', error);
        alert('เกิดข้อผิดพลาดในการสำรองข้อมูล');
    }
}

// Restore data
function restoreData(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const backup = JSON.parse(e.target.result);

            if (!confirm('คุณต้องการนำเข้าข้อมูลสำรองใช่หรือไม่? ข้อมูลปัจจุบันจะถูกแทนที่')) {
                return;
            }

            // Restore settings
            if (backup.settings) {
                if (backup.settings.general) localStorage.setItem('cheque_general_settings', JSON.stringify(backup.settings.general));
                if (backup.settings.numbering) localStorage.setItem('cheque_numbering_settings', JSON.stringify(backup.settings.numbering));
                if (backup.settings.print) localStorage.setItem('cheque_print_settings', JSON.stringify(backup.settings.print));
                if (backup.settings.template) localStorage.setItem('cheque_template', JSON.stringify(backup.settings.template));
            }

            alert('นำเข้าข้อมูลเรียบร้อย กรุณารีเฟรชหน้าเพจ');
            location.reload();
        } catch (error) {
            console.error('Restore failed:', error);
            alert('ไม่สามารถนำเข้าข้อมูลได้ ไฟล์อาจเสียหาย');
        }
    };
    reader.readAsText(file);
}

// Reset system
function resetSystem() {
    if (!confirm('คุณแน่ใจหรือไม่ที่จะรีเซ็ตระบบ? ข้อมูลทั้งหมดจะถูกลบและไม่สามารถกู้คืนได้')) {
        return;
    }

    if (!confirm('กรุณายืนยันอีกครั้ง: การดำเนินการนี้จะลบข้อมูลทั้งหมด')) {
        return;
    }

    localStorage.removeItem('cheque_general_settings');
    localStorage.removeItem('cheque_numbering_settings');
    localStorage.removeItem('cheque_print_settings');
    localStorage.removeItem('cheque_template');

    alert('รีเซ็ตระบบเรียบร้อย กรุณารีเฟรชหน้าเพจ');
    location.reload();
}

// Load saved settings
function loadSettings() {
    // General
    const general = JSON.parse(localStorage.getItem('cheque_general_settings') || '{}');
    if (general.company_name) document.getElementById('company-name').value = general.company_name;
    if (general.date_format) document.getElementById('date-format').value = general.date_format;
    if (general.currency) document.getElementById('currency').value = general.currency;
    if (general.cheque_language) document.getElementById('cheque-language').value = general.cheque_language;
    if (general.auto_backup !== undefined) document.getElementById('auto-backup').checked = general.auto_backup;

    // Numbering
    const numbering = JSON.parse(localStorage.getItem('cheque_numbering_settings') || '{}');
    if (numbering.format) document.getElementById('number-format').value = numbering.format;
    if (numbering.prefix) document.getElementById('number-prefix').value = numbering.prefix;
    if (numbering.padding) document.getElementById('number-padding').value = numbering.padding;
    updateNumberPreview();

    // Print
    const print = JSON.parse(localStorage.getItem('cheque_print_settings') || '{}');
    if (print.paper_size) document.getElementById('paper-size').value = print.paper_size;
    if (print.orientation) document.getElementById('paper-orientation').value = print.orientation;
    if (print.font) document.getElementById('print-font').value = print.font;
    if (print.font_size) document.getElementById('print-font-size').value = print.font_size;
    if (print.auto_print !== undefined) document.getElementById('print-auto').checked = print.auto_print;
    if (print.show_border !== undefined) document.getElementById('print-border').checked = print.show_border;
}

// Initialize
window.addEventListener('DOMContentLoaded', () => {
    loadSettings();
    updateNumberPreview();
});
</script>
@endpush
