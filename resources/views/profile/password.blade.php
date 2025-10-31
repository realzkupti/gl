@extends('tailadmin.layouts.app')

@section('title', 'เปลี่ยนรหัสผ่าน')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
  <div class="mb-6">
    @include('partials.settings-heading')
  </div>

  <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">เปลี่ยนรหัสผ่าน</h2>

    @if(session('status'))
      <div class="mb-4 rounded bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400">{{ session('status') }}</div>
    @endif

    @if($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="post" action="{{ route('user-password.update') }}" class="space-y-4">
      @csrf
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">รหัสผ่านเดิม</label>
        <input name="current_password" type="password" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">รหัสผ่านใหม่</label>
        <input name="password" type="password" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500" />
      </div>
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">ยืนยันรหัสผ่านใหม่</label>
        <input name="password_confirmation" type="password" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500" />
      </div>
      <div class="pt-2">
        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-white hover:bg-brand-700">บันทึกการเปลี่ยนแปลง</button>
      </div>
    </form>
  </div>
</div>
@endsection
