@extends('layouts.app')

@section('content')
<div class="container mx-auto py-10 max-w-md">
    <h1 class="text-2xl font-semibold mb-4">เข้าสู่ระบบ</h1>
    @if($errors->any())
        <div class="mb-3 p-3 rounded bg-red-100 text-red-800">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="/login">
        @csrf
        <div class="mb-3">
            <label class="block mb-1">อีเมล</label>
            <input name="email" type="email" class="border rounded w-full px-3 py-2" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label class="block mb-1">รหัสผ่าน</label>
            <input name="password" type="password" class="border rounded w-full px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="remember" value="1"> จดจำการเข้าสู่ระบบ</label>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">เข้าสู่ระบบ</button>
            <a href="/" class="px-4 py-2 border rounded">กลับหน้าหลัก</a>
        </div>
    </form>
</div>
@endsection

