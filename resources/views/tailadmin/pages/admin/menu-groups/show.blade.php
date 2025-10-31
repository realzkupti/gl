@extends('tailadmin.layouts.app')

@section('title', 'ผู้ใช้ในกลุ่มเมนู: ' . $group->label)

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-semibold text-black dark:text-white">
            ผู้ใช้ในกลุ่มเมนู: {{ $group->label }}
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li>
                    <a class="font-medium" href="{{ route('admin.menu-groups.index') }}">กลุ่มเมนู /</a>
                </li>
                <li class="font-medium text-primary">ผู้ใช้ในกลุ่ม</li>
            </ol>
        </nav>
    </div>

    <div class="flex flex-col gap-5 md:gap-7 2xl:gap-10">
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke px-4 py-4 dark:border-strokedark sm:px-6 xl:px-7.5">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-black dark:text-white">
                        รายชื่อผู้ใช้ที่มีสิทธิ์เข้าถึงเมนูในกลุ่มนี้
                    </h3>
                    <a href="{{ route('admin.menu-groups.index') }}" class="inline-flex items-center justify-center rounded-md border border-stroke px-4 py-2 text-center font-medium text-black hover:bg-gray-50 dark:border-strokedark dark:text-white dark:hover:bg-gray-800">
                        กลับ
                    </a>
                </div>
            </div>

            <div class="p-4 sm:p-6 xl:p-7.5">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-2 text-left dark:bg-meta-4">
                                <th class="min-w-[150px] px-4 py-4 font-medium text-black dark:text-white xl:pl-11">
                                    ชื่อผู้ใช้
                                </th>
                                <th class="min-w-[200px] px-4 py-4 font-medium text-black dark:text-white">
                                    อีเมล
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="border-b border-[#eee] px-4 py-5 pl-9 dark:border-strokedark xl:pl-11">
                                        <h5 class="font-medium text-black dark:text-white">
                                            {{ $user->name }}
                                        </h5>
                                    </td>
                                    <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                                        <p class="text-black dark:text-white">
                                            {{ $user->email }}
                                        </p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="border-b border-[#eee] px-4 py-5 text-center dark:border-strokedark">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            ไม่มีผู้ใช้ที่มีสิทธิ์ในกลุ่มนี้
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
