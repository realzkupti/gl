{{-- Redirect to new TailAdmin dashboard --}}
@php
    // Redirect to new location
    header('Location: ' . route('tailadmin.dashboard'));
    exit;
@endphp

