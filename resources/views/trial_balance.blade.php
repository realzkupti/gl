@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-semibold mb-4">Trial Balance</h1>
        <div class="mb-4">
            <form method="get">
                <label>Company</label>
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
        @livewire('trial-balance')
    </div>
@endsection
