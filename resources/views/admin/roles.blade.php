@extends('tailadmin.layouts.app')

@section('title', 'จัดการกลุ่มผู้ใช้ - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
  <div class="mb-6 flex items-center justify-between">
    <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">จัดการกลุ่มผู้ใช้</h2>
  </div>

  <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <table class="w-full">
      <thead>
        <tr class="text-left text-sm text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
          <th class="py-2">ID</th>
          <th class="py-2">ชื่อกลุ่ม</th>
          <th class="py-2">รายละเอียด</th>
        </tr>
      </thead>
      <tbody>
        @forelse($roles as $role)
          <tr class="border-b border-gray-100 dark:border-gray-800 text-sm">
            <td class="py-2">{{ $role->id }}</td>
            <td class="py-2">{{ $role->name }}</td>
            <td class="py-2">{{ $role->description }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="py-4 text-center text-gray-500">ยังไม่มีข้อมูลกลุ่มผู้ใช้</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

