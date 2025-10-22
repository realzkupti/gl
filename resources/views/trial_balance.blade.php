@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-semibold mb-4">งบทดลอง</h1>
        <div class="mb-4">
            <form method="get">
                <label>บริษัท</label>
                <select name="company" class="border rounded px-2 py-1" onchange="this.form.submit()">
                    @php
                        $companies = \App\Services\CompanyManager::listCompanies();
                        $selectedCompany = \App\Services\CompanyManager::getSelectedKey();
                    @endphp
                    @foreach(($companies ?? []) as $key => $c)
                        @php $label = is_array($c) ? ($c['label'] ?? $key) : $key; @endphp
                        <option value="{{ $key }}" {{ ($selectedCompany === $key) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="mb-3 flex items-center gap-2 print:hidden">
            <button type="button" onclick="window.print()" class="bg-gray-700 text-white px-3 py-1 rounded">พิมพ์</button>
        </div>
        <style>
            @media print {
                .print\:hidden { display: none !important; }
            }
            @page { size: A4 landscape; margin: 10mm; }
        </style>
        @livewire('trial-balance')
    </div>
@endsection
