@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">ตั้งค่าและรายงาน</h1>

    @if(session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-4 border rounded bg-white">
            <h2 class="text-xl font-semibold mb-2">บริษัท</h2>
            <p class="text-sm text-gray-600 mb-3">เลือกบริษัทที่จะเชื่อมต่อฐานข้อมูล</p>
            <form method="get">
                <label class="block mb-1">บริษัท</label>
                <select name="company" class="border rounded px-2 py-1" onchange="this.form.submit()">
                    @php
                        $companies = $companies ?? \App\Services\CompanyManager::listCompanies();
                        $selectedCompany = $selectedCompany ?? \App\Services\CompanyManager::getSelectedKey();
                    @endphp
                    @foreach(($companies ?? []) as $key => $c)
                        @php $label = is_array($c) ? ($c['label'] ?? $key) : $key; @endphp
                        <option value="{{ $key }}" {{ ($selectedCompany === $key) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="p-4 border rounded bg-white">
            <h2 class="text-xl font-semibold mb-2">รายงาน</h2>
            <p class="text-sm text-gray-600 mb-3">เปิดรายงานด้วยบริษัทที่เลือก</p>
            <ul class="list-disc list-inside space-y-2">
                <li>
                    <a class="text-blue-700 underline report-link" href="{{ route('trial-balance.plain') }}">งบทดลอง (แบบธรรมดา)</a>
                </li>
                <li>
                    <a class="text-blue-700 underline report-link" href="{{ route('trial-balance.branch') }}">งบทดลอง (แยกสาขา)</a>
                </li>
                <li>
                    <a class="text-blue-700 underline report-link" href="{{ route('trial-balance') }}">งบทดลอง (แยกสาขา, ต้องล็อกอิน)</a>
                </li>
                <li>
                    <a class="text-blue-700 underline" href="{{ route('cheque.ui') }}">ระบบเช็ค</a>
                </li>
            </ul>
        </div>

        <div class="p-4 border rounded bg-white md:col-span-2">
            <h2 class="text-xl font-semibold mb-2">ตั้งค่าบริษัท (JSON)</h2>
            <p class="text-sm text-gray-600 mb-3">แก้ไข JSON สำหรับกำหนดบริษัทและการเชื่อมต่อ สามารถใช้ ${ENV_VAR} เพื่ออ้างอิงตัวแปร .env</p>
            <form method="post" action="{{ route('settings.companies.save') }}">
                @csrf
                <textarea name="companies_json" class="w-full border rounded p-2 font-mono" rows="12">{{ $companiesJson }}</textarea>
                <div class="mt-3">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.querySelectorAll('.report-link').forEach(function(a){
        a.addEventListener('click', function(e){
            if (window.Swal) {
                window.showLoading('กำลังโหลดรายงาน...');
            }
        });
    });
</script>
@endpush
@endsection
