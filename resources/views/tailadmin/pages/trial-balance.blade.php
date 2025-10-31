@extends('tailadmin.layouts.app')

@section('title', 'งบทดลอง (แยกสาขา)')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            งบทดลอง (แยกสาขา)
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-brand-500">งบทดลอง</li>
            </ol>
        </nav>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">งวดบัญชี</label>
                <select id="period" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500">
                    @foreach($periods ?? [] as $p)
                        <option value="{{ $p->GLP_KEY }}" {{ ($selectedPeriodKey == $p->GLP_KEY) ? 'selected' : '' }}>
                            {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">สาขา</label>
                <select id="branch" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500">
                    <option value="">ทุกสาขา</option>
                    @foreach($branches ?? [] as $b)
                        <option value="{{ $b->code }}">{{ $b->name ?? $b->code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button id="btnLoad" class="w-full rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                    แสดงผล
                </button>
            </div>
        </div>

        <div class="mt-4 flex gap-3 print:hidden">
            <button onclick="window.print()" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="mr-2 inline-block h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                พิมพ์
            </button>

            <button onclick="exportExcel()" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="mr-2 inline-block h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>

            <button onclick="exportPDF()" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="mr-2 inline-block h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table id="tb-branch" class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">เลขบัญชี</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">ชื่อบัญชี</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">สาขา</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดยกมา Dr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดยกมา Cr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดเคลื่อนไหว Dr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดเคลื่อนไหว Cr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดคงเหลือ Dr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดคงเหลือ Cr</th>
                    </tr>
                </thead>
                <tbody id="branch-body">
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">กำลังโหลดข้อมูล...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .print\:hidden { display: none !important; }
    }
    @page { size: A4 landscape; margin: 10mm; }
</style>
@endpush

@push('scripts')
<script>
    function fmt(v){ v=Number(v||0); return v? v.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}): '' }

    async function loadBranch(){
        try{
            const p = document.getElementById('period').value;
            const b = document.getElementById('branch').value;

            const tbody = document.getElementById('branch-body');
            tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">กำลังโหลดข้อมูล...</td></tr>';

            const res = await fetch(`/trial-balance-branch-data?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`);
            const json = await res.json();
            const rows = json.data || [];

            let html = '';
            rows.forEach(r => {
                html += `<tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${r.account_number||''}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${r.account_name||''}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${r.branch_name||''}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.opening_debit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.opening_credit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.movement_debit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.movement_credit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.balance_debit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.balance_credit)}</td>
                </tr>`;
            });

            tbody.innerHTML = html || '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">ไม่พบข้อมูล</td></tr>';
        }catch(e){
            document.getElementById('branch-body').innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-red-500">โหลดข้อมูลไม่สำเร็จ</td></tr>';
            console.error(e);
        }
    }

    function exportExcel() {
        const p = document.getElementById('period').value;
        const b = document.getElementById('branch').value;
        window.location.href = `/trial-balance-excel?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`;
    }

    function exportPDF() {
        const p = document.getElementById('period').value;
        const b = document.getElementById('branch').value;
        window.open(`/trial-balance-pdf?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`, '_blank');
    }

    document.getElementById('btnLoad').addEventListener('click', loadBranch);

    // Load on page ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadBranch);
    } else {
        loadBranch();
    }
</script>
@endpush

{{-- Sticky Note Component --}}
@if(isset($currentMenu) && $currentMenu && $currentMenu->has_sticky_note)
    <x-sticky-note
        :menu-id="$currentMenu->id"
        :company-id="session('current_company_id')"
    />
@endif

@endsection
