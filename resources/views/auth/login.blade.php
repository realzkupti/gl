@extends('tailadmin.layouts.auth')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
  <div class="mx-auto max-w-md">
    <div class="mb-6 text-center">
      <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">เข้าสู่ระบบ</h2>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">ลงชื่อเข้าใช้บัญชีของคุณ</p>
    </div>

    @if($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
      <form method="post" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">อีเมล</label>
          <input name="email" type="email" value="{{ old('email') }}" required class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">รหัสผ่าน</label>
          <input name="password" type="password" required class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500" />
        </div>
        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
            จำการเข้าสู่ระบบ
          </label>
          <a href="{{ url('/') }}" class="text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">กลับหน้าหลัก</a>
        </div>
        <div class="pt-2">
          <button type="submit" class="w-full rounded bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600">เข้าสู่ระบบ</button>
        </div>
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
          ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="text-brand-600 hover:text-brand-700">สมัครสมาชิก</a>
        </div>
      </form>
    </div>
  </div>
 </div>
@endsection

