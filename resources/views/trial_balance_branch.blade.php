@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">งบทดลอง (แยกสาขา — Blade + JS)</h1>

    <div class="mb-4 flex items-end gap-4">
        <div>
            <label>งวดบัญชี</label>
            <select id="period" class="border rounded px-2 py-1">
                @foreach($periods ?? [] as $p)
                    <option value="{{ $p->GLP_KEY }}" {{ ($selectedPeriodKey == $p->GLP_KEY) ? 'selected' : '' }}>
                        {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label>สาขา</label>
            <select id="branch" class="border rounded px-2 py-1">
                <option value="">ทุกสาขา</option>
                @foreach($branches ?? [] as $b)
                    <option value="{{ $b->code }}">{{ $b->name ?? $b->code }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button id="btnLoad" class="bg-blue-600 text-white px-3 py-1 rounded">แสดงผล</button>
        </div>
    </div>

    <div class="overflow-auto">
        <table id="tb-branch" class="min-w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">เลขบัญชี</th>
                    <th class="border px-2 py-1">ชื่อบัญชี</th>
                    <th class="border px-2 py-1">สาขา</th>
                    <th class="border px-2 py-1 text-right">ยอดยกมา Dr</th>
                    <th class="border px-2 py-1 text-right">ยอดยกมา Cr</th>
                    <th class="border px-2 py-1 text-right">ยอดเคลื่อนไหว Dr</th>
                    <th class="border px-2 py-1 text-right">ยอดเคลื่อนไหว Cr</th>
                    <th class="border px-2 py-1 text-right">ยอดคงเหลือ Dr</th>
                    <th class="border px-2 py-1 text-right">ยอดคงเหลือ Cr</th>
                </tr>
            </thead>
            <tbody id="branch-body"></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    function fmt(v){ v=Number(v||0); return v? v.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}): '' }
    async function loadBranch(){
        try{
            const p = document.getElementById('period').value;
            const b = document.getElementById('branch').value;
            if (window.showLoading) showLoading('กำลังโหลด...');
            const res = await fetch(`/trial-balance-branch-data?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`);
            const json = await res.json();
            const rows = json.data || [];
            let html = '';
            rows.forEach(r => {
                html += `<tr>
                    <td class="border px-2 py-1">${r.account_number||''}</td>
                    <td class="border px-2 py-1">${r.account_name||''}</td>
                    <td class="border px-2 py-1">${r.branch_name||''}</td>
                    <td class="border px-2 py-1 text-right">${fmt(r.opening_debit)}</td>
                    <td class="border px-2 py-1 text-right">${fmt(r.opening_credit)}</td>
                    <td class="border px-2 py-1 text-right">${fmt(r.movement_debit)}</td>
                    <td class="border px-2 py-1 text-right">${fmt(r.movement_credit)}</td>
                    <td class="border px-2 py-1 text-right">${fmt(r.balance_debit)}</td>
                    <td class="border px-2 py-1 text-right">${fmt(r.balance_credit)}</td>
                </tr>`;
            });
            document.getElementById('branch-body').innerHTML = html || '<tr><td colspan="9" class="border px-2 py-2 text-center text-gray-500">ไม่พบข้อมูล</td></tr>';
        }catch(e){ if(window.showError) showError('โหลดข้อมูลไม่สำเร็จ'); }
        finally{ if(window.Swal) Swal.close(); }
    }
    document.getElementById('btnLoad').addEventListener('click', loadBranch);
    loadBranch();
</script>
@endpush
@endsection
