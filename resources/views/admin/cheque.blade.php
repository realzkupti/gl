@extends('layouts.admin')

@section('content')
<div class="h-[calc(100vh-140px)]">
    <iframe src="{{ route('cheque.ui') }}" class="w-full h-full border rounded bg-white"></iframe>
    <p class="mt-2 text-sm text-gray-600">หมายเหตุ: หน้านี้ฝัง UI เดิมผ่าน iframe เพื่อคงการทำงานและ CSS 100% แยกจาก Admin CSS</p>
    <p class="text-sm text-gray-600">หากต้องการย้าย UI ทั้งหมดมาเป็น Blade ในอนาคต ผมสามารถ refactor ให้ได้</p>
    <link rel="preload" href="{{ route('cheque.css') }}" as="style">
</div>
@endsection

