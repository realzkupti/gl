@extends('tailadmin.layouts.app')

@section('title', 'โปรไฟล์ของฉัน')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
  <div class="mb-6">
    @include('partials.settings-heading')
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile card -->
    <div class="lg:col-span-1 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <div class="flex items-center gap-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-500 text-white text-2xl font-bold">
          {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
        </div>
        <div>
          <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</div>
          <div class="text-sm text-gray-600 dark:text-gray-400">{{ auth()->user()->email }}</div>
        </div>
      </div>
      <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
        @php
          $passwordUrl = \Illuminate\Support\Facades\Route::has('user-password.edit')
            ? route('user-password.edit')
            : (\Illuminate\Support\Facades\Route::has('password.edit') ? route('password.edit') : '#');
          $twoFactorUrl = \Illuminate\Support\Facades\Route::has('two-factor.show')
            ? route('two-factor.show')
            : '#';
        @endphp
        <a href="{{ $passwordUrl }}" class="rounded-lg bg-gray-100 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-200 transition dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">เปลี่ยนรหัสผ่าน</a>
        
      </div>
    </div>

    <!-- Details table -->
    <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ข้อมูลบัญชี</h3>
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">ฟิลด์</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">ข้อมูล</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">รหัสผู้ใช้</td>
              <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ auth()->id() }}</td>
            </tr>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">ชื่อ</td>
              <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</td>
            </tr>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">อีเมล</td>
              <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->email }}</td>
            </tr>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">สร้างเมื่อ</td>
              <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ optional(auth()->user()->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
            </tr>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">สถานะ</td>
              <td class="px-4 py-3 text-sm">
                @php($isActive = auth()->user()->is_active ?? true)
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                  {{ $isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }}">
                  {{ $isActive ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
