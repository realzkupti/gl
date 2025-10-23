@extends('tailadmin.layouts.app')

@section('title', 'ออกแบบ & ปรับแต่ง - ระบบเช็ค')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">ออกแบบ & ปรับแต่งเช็ค</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('tailadmin.dashboard') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">Dashboard /</a></li>
                <li><a href="{{ route('cheque.print') }}" class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">ออกแบบ</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Left: Settings -->
        <div class="space-y-6">
            <!-- Template Selection -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">เลือกเทมเพลต</h3>
                <select id="template-select" class="w-full rounded border border-gray-300 px-4 py-2.5 dark:border-gray-700">
                    <option value="standard">เทมเพลตมาตรฐาน</option>
                    <option value="kbank">ธนาคารกสิกรไทย</option>
                    <option value="scb">ธนาคารไทยพาณิชย์</option>
                    <option value="bbl">ธนาคารกรุงเทพ</option>
                    <option value="custom">กำหนดเอง</option>
                </select>
            </div>

            <!-- Position Settings -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">ปรับตำแหน่ง</h3>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium">เลขที่เช็ค (X)</label>
                        <input type="number" id="number-x" value="500" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">เลขที่เช็ค (Y)</label>
                        <input type="number" id="number-y" value="20" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">วันที่ (X)</label>
                        <input type="number" id="date-x" value="500" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">วันที่ (Y)</label>
                        <input type="number" id="date-y" value="50" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ผู้รับเงิน (X)</label>
                        <input type="number" id="payee-x" value="100" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">ผู้รับเงิน (Y)</label>
                        <input type="number" id="payee-y" value="100" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">จำนวนเงิน (X)</label>
                        <input type="number" id="amount-x" value="450" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium">จำนวนเงิน (Y)</label>
                        <input type="number" id="amount-y" value="160" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button onclick="saveTemplate()" class="flex-1 rounded bg-brand-500 px-4 py-2 text-white hover:bg-brand-600">
                        บันทึก
                    </button>
                    <button onclick="resetTemplate()" class="rounded border px-4 py-2 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                        รีเซ็ต
                    </button>
                </div>
            </div>

            <!-- Font Settings -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">ฟอนต์และขนาด</h3>

                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium">ฟอนต์</label>
                        <select class="w-full rounded border px-4 py-2 dark:border-gray-700">
                            <option>TH Sarabun New</option>
                            <option>Angsana New</option>
                            <option>Cordia New</option>
                            <option>Arial</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">ขนาดฟอนต์</label>
                        <input type="number" value="14" class="w-full rounded border px-4 py-2 dark:border-gray-700" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Live Preview -->
        <div>
            <div class="sticky top-24 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">ตัวอย่างสด</h3>

                <div class="aspect-[3/1] border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-800">
                    <div id="live-preview" class="relative h-full rounded-lg border-2 border-gray-400 bg-white p-6 shadow-lg" style="font-family: 'TH Sarabun New', sans-serif;">
                        <!-- Dynamic positioning -->
                        <div class="absolute" id="live-number" style="right: 20px; top: 20px;">000000</div>
                        <div class="absolute" id="live-date" style="right: 20px; top: 50px;">01/01/2567</div>
                        <div class="absolute" id="live-payee" style="left: 100px; top: 100px;">นายทดสอบ ระบบ</div>
                        <div class="absolute" id="live-amount-text" style="left: 100px; top: 140px;">หนึ่งหมื่นบาทถ้วน</div>
                        <div class="absolute" id="live-amount" style="right: 30px; bottom: 30px; font-size: 20px; font-weight: bold;">10,000.00</div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    * ปรับค่าทางซ้ายเพื่อดูการเปลี่ยนแปลงแบบทันที
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updatePreview() {
    const numberX = document.getElementById('number-x').value;
    const numberY = document.getElementById('number-y').value;
    const dateX = document.getElementById('date-x').value;
    const dateY = document.getElementById('date-y').value;
    const payeeX = document.getElementById('payee-x').value;
    const payeeY = document.getElementById('payee-y').value;
    const amountX = document.getElementById('amount-x').value;
    const amountY = document.getElementById('amount-y').value;

    document.getElementById('live-number').style.right = numberX + 'px';
    document.getElementById('live-number').style.top = numberY + 'px';
    document.getElementById('live-date').style.right = dateX + 'px';
    document.getElementById('live-date').style.top = dateY + 'px';
    document.getElementById('live-payee').style.left = payeeX + 'px';
    document.getElementById('live-payee').style.top = payeeY + 'px';
    document.getElementById('live-amount').style.right = amountX + 'px';
    document.getElementById('live-amount').style.bottom = amountY + 'px';
}

function saveTemplate() {
    const config = {
        number: { x: document.getElementById('number-x').value, y: document.getElementById('number-y').value },
        date: { x: document.getElementById('date-x').value, y: document.getElementById('date-y').value },
        payee: { x: document.getElementById('payee-x').value, y: document.getElementById('payee-y').value },
        amount: { x: document.getElementById('amount-x').value, y: document.getElementById('amount-y').value },
    };

    localStorage.setItem('cheque_template', JSON.stringify(config));
    alert('บันทึกเทมเพลตเรียบร้อย');
}

function resetTemplate() {
    document.getElementById('number-x').value = 500;
    document.getElementById('number-y').value = 20;
    document.getElementById('date-x').value = 500;
    document.getElementById('date-y').value = 50;
    document.getElementById('payee-x').value = 100;
    document.getElementById('payee-y').value = 100;
    document.getElementById('amount-x').value = 450;
    document.getElementById('amount-y').value = 160;
    updatePreview();
}

// Add listeners
['number-x', 'number-y', 'date-x', 'date-y', 'payee-x', 'payee-y', 'amount-x', 'amount-y'].forEach(id => {
    document.getElementById(id).addEventListener('input', updatePreview);
});

// Load saved template
const saved = localStorage.getItem('cheque_template');
if (saved) {
    const config = JSON.parse(saved);
    if (config.number) {
        document.getElementById('number-x').value = config.number.x;
        document.getElementById('number-y').value = config.number.y;
    }
    if (config.date) {
        document.getElementById('date-x').value = config.date.x;
        document.getElementById('date-y').value = config.date.y;
    }
    if (config.payee) {
        document.getElementById('payee-x').value = config.payee.x;
        document.getElementById('payee-y').value = config.payee.y;
    }
    if (config.amount) {
        document.getElementById('amount-x').value = config.amount.x;
        document.getElementById('amount-y').value = config.amount.y;
    }
    updatePreview();
}
</script>
@endpush
