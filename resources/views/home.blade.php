@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">Setup & Reports</h1>

    @if(session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-4 border rounded bg-white">
            <h2 class="text-xl font-semibold mb-2">Company</h2>
            <p class="text-sm text-gray-600 mb-3">Select the company to use for database connection.</p>
            <form method="get">
                <label class="block mb-1">Company</label>
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
            <h2 class="text-xl font-semibold mb-2">Reports</h2>
            <p class="text-sm text-gray-600 mb-3">Open a report using the selected company.</p>
            <ul class="list-disc list-inside space-y-2">
                <li>
                    <a class="text-blue-700 underline" href="{{ route('trial-balance.plain') }}">Trial Balance (Plain)</a>
                </li>
                <li>
                    <a class="text-blue-700 underline" href="{{ route('trial-balance.open') }}">Trial Balance (Livewire, no auth)</a>
                </li>
                <li>
                    <a class="text-blue-700 underline" href="{{ route('trial-balance') }}">Trial Balance (Livewire, auth)</a>
                </li>
            </ul>
        </div>

        <div class="p-4 border rounded bg-white md:col-span-2">
            <h2 class="text-xl font-semibold mb-2">Companies Configuration</h2>
            <p class="text-sm text-gray-600 mb-3">Edit the JSON used to define companies and database connections. Use ${ENV_VAR} to reference .env values.</p>
            <form method="post" action="{{ route('settings.companies.save') }}">
                @csrf
                <textarea name="companies_json" class="w-full border rounded p-2 font-mono" rows="12">{{ $companiesJson }}</textarea>
                <div class="mt-3">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

