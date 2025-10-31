@extends('tailadmin.layouts.app')

@section('title', 'จัดการกลุ่มเมนู')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-semibold text-black dark:text-white">
            จัดการกลุ่มเมนู
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-primary">กลุ่มเมนู</li>
            </ol>
        </nav>
    </div>

    <div class="flex flex-col gap-5 md:gap-7 2xl:gap-10">
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke px-4 py-4 dark:border-strokedark sm:px-6 xl:px-7.5">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-black dark:text-white">
                        รายการกลุ่มเมนู
                    </h3>
                    <a href="{{ route('admin.menu-groups.create') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-center font-medium text-white hover:bg-opacity-90">
                        เพิ่มกลุ่มใหม่
                    </a>
                </div>
            </div>

            <div class="p-4 sm:p-6 xl:p-7.5">
                @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4 text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4 text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-2 text-left dark:bg-meta-4">
                                <th class="min-w-[150px] px-4 py-4 font-medium text-black dark:text-white xl:pl-11">
                                    Key
                                </th>
                                <th class="min-w-[150px] px-4 py-4 font-medium text-black dark:text-white">
                                    ชื่อแสดง
                                </th>
                                <th class="min-w-[120px] px-4 py-4 font-medium text-black dark:text-white">
                                    ลำดับ
                                </th>
                                <th class="min-w-[120px] px-4 py-4 font-medium text-black dark:text-white">
                                    สถานะ
                                </th>
                                <th class="px-4 py-4 font-medium text-black dark:text-white">
                                    การดำเนินการ
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $group)
                                <tr>
                                    <td class="border-b border-[#eee] px-4 py-5 pl-9 dark:border-strokedark xl:pl-11">
                                        <h5 class="font-medium text-black dark:text-white">
                                            {{ $group->key }}
                                        </h5>
                                    </td>
                                    <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                                        <p class="text-black dark:text-white">
                                            {{ $group->label }}
                                        </p>
                                    </td>
                                    <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                                        <p class="text-black dark:text-white">
                                            {{ $group->sort_order }}
                                        </p>
                                    </td>
                                    <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                                        <p class="text-black dark:text-white">
                                            {{ $group->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                        </p>
                                    </td>
                                    <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark">
                                        <div class="flex items-center space-x-3.5">
                                            <a href="{{ route('admin.menu-groups.show', $group->id) }}" class="hover:text-primary">
                                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M8.99981 14.8219C3.43106 14.8219 0.674805 9.50624 0.562305 9.28124C0.47793 9.11249 0.47793 8.88749 0.562305 8.71874C0.674805 8.49374 3.43106 3.20624 8.99981 3.20624C14.5686 3.20624 17.3248 8.49374 17.4373 8.71874C17.5217 8.88749 17.5217 9.11249 17.4373 9.28124C17.3248 9.50624 14.5686 14.8219 8.99981 14.8219ZM1.85605 8.99999C2.4748 10.0406 4.89306 13.5562 8.99981 13.5562C13.1066 13.5562 15.5248 10.0406 16.1436 8.99999C15.5248 7.95936 13.1066 4.44374 8.99981 4.44374C4.89306 4.44374 2.4748 7.95936 1.85605 8.99999Z"/>
                                                    <path d="M9 11.3906C7.67812 11.3906 6.60938 10.3219 6.60938 9C6.60938 7.67813 7.67812 6.60938 9 6.60938C10.3219 6.60938 11.3906 7.67813 11.3906 9C11.3906 10.3219 10.3219 11.3906 9 11.3906ZM9 7.875C8.38125 7.875 7.875 8.38125 7.875 9C7.875 9.61875 8.38125 10.125 9 10.125C9.61875 10.125 10.125 9.61875 10.125 9C10.125 8.38125 9.61875 7.875 9 7.875Z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.menu-groups.edit', $group->id) }}" class="hover:text-primary">
                                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.7535 2.47502C14.1111 2.11666 14.5887 2.11666 14.9463 2.47502L15.5249 3.05358C15.8833 3.41201 15.8833 3.88961 15.5249 4.24805L7.08185 12.6911C6.92139 12.8516 6.70433 12.9414 6.47807 12.9414L3.33057 12.9414C2.98818 12.9414 2.71106 12.6643 2.71106 12.3219L2.71106 9.1744C2.71106 8.94814 2.80086 8.73108 2.96133 8.57061L10.4044 1.12752C10.7628 0.769161 11.2404 0.769161 11.5979 1.12752L13.7535 2.47502Z"/>
                                                    <path d="M11.9156 3.92661L14.0731 6.08411L5.40214 14.7551C5.24167 14.9156 5.02461 15.0054 4.79835 15.0054L1.65085 15.0054C1.30846 15.0054 1.03134 14.7283 1.03134 14.3859L1.03134 11.2384C1.03134 11.0121 1.12114 10.7951 1.28161 10.6346L9.95257 1.96361L11.9156 3.92661Z"/>
                                                </svg>
                                            </a>
                                            @if(!$group->is_default)
                                                <form method="POST" action="{{ route('admin.menu-groups.destroy', $group->id) }}" class="inline" onsubmit="return confirm('คุณต้องการลบกลุ่มนี้หรือไม่?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="hover:text-red-500">
                                                        <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.7535 2.47502C14.1111 2.11666 14.5887 2.11666 14.9463 2.47502L15.5249 3.05358C15.8833 3.41201 15.8833 3.88961 15.5249 4.24805L7.08185 12.6911C6.92139 12.8516 6.70433 12.9414 6.47807 12.9414L3.33057 12.9414C2.98818 12.9414 2.71106 12.6643 2.71106 12.3219L2.71106 9.1744C2.71106 8.94814 2.80086 8.73108 2.96133 8.57061L10.4044 1.12752C10.7628 0.769161 11.2404 0.769161 11.5979 1.12752L13.7535 2.47502Z"/>
                                                            <path d="M11.9156 3.92661L14.0731 6.08411L5.40214 14.7551C5.24167 14.9156 5.02461 15.0054 4.79835 15.0054L1.65085 15.0054C1.30846 15.0054 1.03134 14.7283 1.03134 14.3859L1.03134 11.2384C1.03134 11.0121 1.12114 10.7951 1.28161 10.6346L9.95257 1.96361L11.9156 3.92661Z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection