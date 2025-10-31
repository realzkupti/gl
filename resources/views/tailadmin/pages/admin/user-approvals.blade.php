@extends('tailadmin.layouts.app')
@php($page = 'admin-user-approvals')

@section('title', 'อนุมัติผู้ใช้ - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
  <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">อนุมัติผู้ใช้</h2>
    <nav>
      <ol class="flex items-center gap-2">
        <li>
          <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
        </li>
        <li class="font-medium text-brand-500">อนุมัติผู้ใช้</li>
      </ol>
    </nav>
  </div>

  @if(session('status'))
    <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('status') }}</div>
  @endif

  <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-7.5 py-4 dark:border-gray-800">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">รายชื่อผู้ใช้</h3>
    </div>
    <div class="p-7.5">
      <div class="overflow-x-auto">
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-50 text-left dark:bg-gray-800">
              <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">ID</th>
              <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">ชื่อ</th>
              <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">อีเมล</th>
              <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">สถานะ</th>
              <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">จัดการ</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $u)
            <tr class="border-b border-gray-200 dark:border-gray-800">
              <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $u->id }}</td>
              <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $u->name }}</td>
              <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $u->email }}</td>
              <td class="px-4 py-3">
                @if($u->is_active)
                  <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">ใช้งาน</span>
                @else
                  <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-sm font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">รออนุมัติ</span>
                @endif
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  @if(!$u->is_active)
                  <form method="post" action="{{ route('admin.user-approvals.activate', $u->id) }}">
                    @csrf
                    <button class="rounded bg-brand-500 px-3 py-1.5 text-sm text-white hover:bg-brand-600">อนุมัติ</button>
                  </form>
                  @else
                  <form method="post" action="{{ route('admin.user-approvals.deactivate', $u->id) }}">
                    @csrf
                    <button class="rounded bg-gray-200 px-3 py-1.5 text-sm text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">ปิดการใช้งาน</button>
                  </form>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-gray-600 dark:text-gray-400">ยังไม่มีผู้ใช้</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
