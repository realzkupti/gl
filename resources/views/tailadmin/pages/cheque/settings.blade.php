@extends('tailadmin.layouts.app')

@section('title', 'ตั้งค่าระบบ - ' . config('app.name'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            ⚙️ ตั้งค่าระบบ
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('cheque.print') }}">ระบบเช็ค /</a></li>
                <li class="font-medium text-brand-500">ตั้งค่า</li>
            </ol>
        </nav>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Left Column -->
        <div class="space-y-6">
            <!-- System Info -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ℹ️ ข้อมูลระบบ</h3>

                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">จำนวนเช็คทั้งหมด</span>
                        <span class="font-semibold text-gray-900 dark:text-white" id="total_cheques">0</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">จำนวนสาขา</span>
                        <span class="font-semibold text-gray-900 dark:text-white" id="total_branches">0</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-400">เทมเพลตที่บันทึก</span>
                        <span class="font-semibold text-gray-900 dark:text-white" id="total_templates">0</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-400">ผู้รับเงินที่ไม่ซ้ำกัน</span>
                        <span class="font-semibold text-gray-900 dark:text-white" id="unique_payees">0</span>
                    </div>
                </div>

                <button onclick="updateSystemInfo()" class="mt-4 w-full rounded bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600">
                    🔄 อัปเดตข้อมูล
                </button>
            </div>

            <!-- Export Data -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📤 ส่งออกข้อมูล</h3>

                <div class="space-y-3">
                    <button onclick="exportCheques()" class="w-full rounded bg-green-500 px-4 py-2.5 text-white hover:bg-green-600 flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        ส่งออกข้อมูลเช็ค (CSV)
                    </button>

                    <button onclick="exportPositions()" class="w-full rounded bg-blue-500 px-4 py-2.5 text-white hover:bg-blue-600 flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        ส่งออกตำแหน่งเช็ค (JSON)
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Database Connection Test -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🩺 ตรวจสอบการเชื่อมต่อ</h3>

                <button onclick="testConnection()" class="w-full rounded bg-purple-500 px-4 py-2.5 text-white hover:bg-purple-600 flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    ทดสอบการเชื่อมต่อฐานข้อมูล
                </button>

                <div id="connection_result" class="mt-4 hidden p-4 rounded-lg"></div>
            </div>

            <!-- Clear Data -->
            <div class="rounded-lg border border-red-200 bg-red-50 p-6 shadow-sm dark:border-red-800 dark:bg-red-900/20">
                <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">🗑️ ล้างข้อมูล</h3>

                <div class="bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-lg p-3 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        ⚠️ <strong>คำเตือน:</strong> การล้างข้อมูลไม่สามารถย้อนกลับได้!
                    </p>
                </div>

                <div class="space-y-3">
                    <button onclick="clearPositions()" class="w-full rounded bg-orange-500 px-4 py-2.5 text-white hover:bg-orange-600">
                        🔄 ล้างตำแหน่งเช็คที่บันทึก
                    </button>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🔗 ลิงก์ด่วน</h3>

                <div class="space-y-2">
                    <a href="{{ route('cheque.print') }}" class="block w-full rounded bg-brand-500 px-4 py-2.5 text-center text-white hover:bg-brand-600">
                        🖨️ ไปหน้าพิมพ์เช็ค
                    </a>
                    <a href="{{ route('cheque.designer') }}" class="block w-full rounded bg-gray-500 px-4 py-2.5 text-center text-white hover:bg-gray-600">
                        🎨 ไปหน้าออกแบบ
                    </a>
                    <a href="{{ route('cheque.reports') }}" class="block w-full rounded bg-blue-500 px-4 py-2.5 text-center text-white hover:bg-blue-600">
                        📊 ไปหน้ารายงาน
                    </a>
                    <a href="{{ route('cheque.branches') }}" class="block w-full rounded bg-green-500 px-4 py-2.5 text-center text-white hover:bg-green-600">
                        🏢 ไปหน้าจัดการสาขา
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '/api';

// Load system info on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSystemInfo();
});

// Update system info
async function updateSystemInfo() {
    try {
        // Fetch all data in parallel
        const [chequesRes, branchesRes, templatesRes] = await Promise.all([
            fetch(`${API_BASE}/cheques`),
            fetch(`${API_BASE}/branches`),
            fetch(`${API_BASE}/templates`)
        ]);

        const cheques = await chequesRes.json();
        const branches = await branchesRes.json();
        const templates = await templatesRes.json();

        // Update counts
        document.getElementById('total_cheques').textContent = cheques.length.toLocaleString('th-TH');
        document.getElementById('total_branches').textContent = branches.length.toLocaleString('th-TH');
        document.getElementById('total_templates').textContent = templates.length.toLocaleString('th-TH');

        // Count unique payees
        const uniquePayees = new Set(cheques.map(c => c.payee).filter(p => p));
        document.getElementById('unique_payees').textContent = uniquePayees.size.toLocaleString('th-TH');

    } catch (error) {
        console.error('Error loading system info:', error);
    }
}

// Test database connection
async function testConnection() {
    const resultDiv = document.getElementById('connection_result');
    resultDiv.className = 'mt-4 p-4 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200';
    resultDiv.textContent = 'กำลังทดสอบ...';
    resultDiv.classList.remove('hidden');

    try {
        const response = await fetch(`${API_BASE}/branches`);
        const data = await response.json();

        if (response.ok) {
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200';
            resultDiv.textContent = `✓ เชื่อมต่อฐานข้อมูลสำเร็จ (พบข้อมูล ${data.length} สาขา)`;
        } else {
            throw new Error('Connection failed');
        }
    } catch (error) {
        resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200';
        resultDiv.textContent = '✗ ไม่สามารถเชื่อมต่อฐานข้อมูลได้';
    }
}

// Export cheques to CSV
async function exportCheques() {
    try {
        const response = await fetch(`${API_BASE}/cheques`);
        const cheques = await response.json();

        if (cheques.length === 0) {
            Swal.fire({
                title: 'ไม่มีข้อมูล!',
                text: 'ยังไม่มีข้อมูลเช็คสำหรับ Export',
                icon: 'warning',
                confirmButtonColor: '#ff9800'
            });
            return;
        }

        // Create CSV
        let csvContent = 'data:text/csv;charset=utf-8,\uFEFF';
        csvContent += 'รหัสสาขา,ธนาคาร,เลขที่เช็ค,วันที่,ผู้รับเงิน,จำนวนเงิน,พิมพ์เมื่อ\n';

        cheques.forEach(row => {
            const date = row.date ? new Date(row.date).toLocaleDateString('th-TH') : '';
            const printed = row.printed_at ? new Date(row.printed_at).toLocaleString('th-TH') : '';
            const amount = parseFloat(row.amount || 0).toFixed(2);

            csvContent += `"${row.branch_code}","${row.bank}","${row.cheque_number}","${date}","${row.payee}","${amount}","${printed}"\n`;
        });

        // Download
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `cheques-export-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        Swal.fire({
            title: 'Export สำเร็จ!',
            text: 'ดาวน์โหลดไฟล์เรียบร้อยแล้ว',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error('Error exporting cheques:', error);
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่สามารถ Export ข้อมูลได้',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Export positions
function exportPositions() {
    const positions = localStorage.getItem('chequePositions');

    if (!positions) {
        Swal.fire({
            title: 'ไม่มีข้อมูล!',
            text: 'ยังไม่มีตำแหน่งที่บันทึกไว้',
            icon: 'warning',
            confirmButtonColor: '#ff9800'
        });
        return;
    }

    const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(positions);
    const link = document.createElement('a');
    link.setAttribute('href', dataStr);
    link.setAttribute('download', `cheque-positions-${new Date().toISOString().split('T')[0]}.json`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    Swal.fire({
        title: 'Export สำเร็จ!',
        text: 'ดาวน์โหลดไฟล์เรียบร้อยแล้ว',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

// Clear positions
function clearPositions() {
    Swal.fire({
        title: 'ล้างตำแหน่งที่บันทึก?',
        text: 'ตำแหน่งที่บันทึกไว้ทั้งหมดจะถูกลบ',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'ใช่, ล้างเลย',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            localStorage.removeItem('chequePositions');
            localStorage.removeItem('paperSize');
            localStorage.removeItem('chequeFormData');

            Swal.fire({
                title: 'ล้างข้อมูลแล้ว!',
                text: 'ตำแหน่งที่บันทึกถูกลบเรียบร้อยแล้ว',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endpush
