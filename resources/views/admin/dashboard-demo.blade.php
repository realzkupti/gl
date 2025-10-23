@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-semibold">Dashboard Demo</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">รายได้ (เดือนนี้)</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($metrics['revenue'], 2) }} ฿</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">ค่าใช้จ่าย (เดือนนี้)</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($metrics['expense'], 2) }} ฿</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">กำไรประมาณการ</div>
            <div class="text-2xl font-bold mt-1">{{ number_format($metrics['profit'], 2) }} ฿</div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="text-sm text-gray-500">ลูกค้าใหม่</div>
            <div class="text-2xl font-bold mt-1">{{ $metrics['customers'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 p-4 bg-white dark:bg-gray-800 rounded shadow min-h-[260px]">
            <div class="flex items-center justify-between">
                <div class="font-semibold">ยอดขายรายวัน</div>
                <span class="text-xs text-gray-500">(ตัวอย่างกราฟ)</span>
            </div>
            <div class="mt-4 h-48 flex items-end gap-2">
                <div class="bg-blue-500/70 w-6 h-12 rounded"></div>
                <div class="bg-blue-500/70 w-6 h-24 rounded"></div>
                <div class="bg-blue-500/70 w-6 h-16 rounded"></div>
                <div class="bg-blue-500/70 w-6 h-28 rounded"></div>
                <div class="bg-blue-500/70 w-6 h-20 rounded"></div>
                <div class="bg-blue-500/70 w-6 h-32 rounded"></div>
                <div class="bg-blue-500/70 w-6 h-14 rounded"></div>
            </div>
        </div>
        <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">
            <div class="font-semibold">กิจกรรมล่าสุด</div>
            <ul class="mt-3 space-y-2">
                @foreach($activities as $a)
                <li class="flex items-start gap-2">
                    <span class="text-xs text-gray-500 mt-1">{{ $a['time'] }}</span>
                    <span>{{ $a['text'] }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

