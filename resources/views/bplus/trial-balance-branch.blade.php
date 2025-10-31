@extends('tailadmin.layouts.app')

@section('title', 'งบทดลองแยกสาขา')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">งบทดลองแยกสาขา</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-brand-500">งบทดลองแยกสาขา</li>
            </ol>
        </nav>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 print:hidden">
        <form class="flex flex-col gap-4 md:flex-row md:items-end md:gap-6">
            <div class="flex-1">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">งวดบัญชี</label>
                <select id="period" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @foreach($periods ?? [] as $p)
                        <option value="{{ $p->GLP_KEY }}" {{ ($selectedPeriodKey == $p->GLP_KEY) ? 'selected' : '' }}>
                            {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">สาขา</label>
                <select id="branch" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">ทุกสาขา</option>
                    @foreach($branches ?? [] as $b)
                        <option value="{{ $b->code }}">{{ $b->name ?? $b->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="button" id="btnLoad" class="rounded-lg bg-blue-600 dark:bg-blue-500 px-6 py-2 text-white hover:bg-blue-700 dark:hover:bg-blue-600 transition font-medium shadow-sm">แสดงผล</button>
            </div>
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-auto">
            <table id="tb-branch" class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">เลขบัญชี</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">ชื่อบัญชี</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-gray-900 dark:text-white">สาขา</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">ยอดยกมา Dr</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">ยอดยกมา Cr</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">ยอดเคลื่อนไหว Dr</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">ยอดเคลื่อนไหว Cr</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">ยอดคงเหลือ Dr</th>
                        <th class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right text-gray-900 dark:text-white">ยอดคงเหลือ Cr</th>
                    </tr>
                </thead>
                <tbody id="branch-body" class="text-gray-900 dark:text-gray-100">
                    <tr>
                        <td colspan="9" class="border border-gray-300 dark:border-gray-600 px-2 py-4 text-center text-gray-500 dark:text-gray-400">
                            กำลังโหลดข้อมูล...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function fmt(v){ v=Number(v||0); return v? v.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}): '' }

    async function loadBranch(){
        try{
            const p = document.getElementById('period').value;
            const b = document.getElementById('branch').value;

            // Show loading toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                didOpen: (toast) => {
                    Swal.showLoading();
                }
            });
            Toast.fire({ title: 'กำลังโหลด...' });

            const res = await fetch(`{{ url('bplus/trial-balance-branch-data') }}?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`);
            const json = await res.json();
            const rows = json.data || [];

            let html = '';
            rows.forEach(r => {
                html += `<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">${r.account_number||''}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">${r.account_name||''}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1">${r.branch_name||''}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">${fmt(r.opening_debit)}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">${fmt(r.opening_credit)}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">${fmt(r.movement_debit)}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">${fmt(r.movement_credit)}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">${fmt(r.balance_debit)}</td>
                    <td class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-right">${fmt(r.balance_credit)}</td>
                </tr>`;
            });

            document.getElementById('branch-body').innerHTML = html || '<tr><td colspan="9" class="border border-gray-300 dark:border-gray-600 px-2 py-4 text-center text-gray-500 dark:text-gray-400">ไม่พบข้อมูล</td></tr>';

            Swal.close();
        }catch(e){
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'โหลดข้อมูลไม่สำเร็จ'
            });
        }
    }

    document.getElementById('btnLoad').addEventListener('click', loadBranch);
    // Auto-load on page load
    document.addEventListener('DOMContentLoaded', loadBranch);
</script>
@endsection
