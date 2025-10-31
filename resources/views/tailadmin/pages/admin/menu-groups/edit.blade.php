@extends('tailadmin.layouts.app')

@section('title', 'แก้ไขกลุ่มเมนู')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-semibold text-black dark:text-white">
            แก้ไขกลุ่มเมนู
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li>
                    <a class="font-medium" href="{{ route('admin.menu-groups.index') }}">กลุ่มเมนู /</a>
                </li>
                <li class="font-medium text-primary">แก้ไข</li>
            </ol>
        </nav>
    </div>

    <div class="flex flex-col gap-5 md:gap-7 2xl:gap-10">
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="border-b border-stroke px-4 py-4 dark:border-strokedark sm:px-6 xl:px-7.5">
                <h3 class="font-medium text-black dark:text-white">
                    ข้อมูลกลุ่มเมนู
                </h3>
            </div>

            <div class="p-4 sm:p-6 xl:p-7.5">
                <form method="POST" action="{{ route('admin.menu-groups.update', $group->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="key" class="mb-2.5 block font-medium text-black dark:text-white">
                            Key <span class="text-meta-1">*</span>
                        </label>
                        <input
                            type="text"
                            id="key"
                            name="key"
                            value="{{ old('key', $group->key) }}"
                            class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                            placeholder="เช่น default, accounting"
                            required
                        />
                        @error('key')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="label" class="mb-2.5 block font-medium text-black dark:text-white">
                            ชื่อแสดง <span class="text-meta-1">*</span>
                        </label>
                        <input
                            type="text"
                            id="label"
                            name="label"
                            value="{{ old('label', $group->label) }}"
                            class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                            placeholder="เช่น เมนู, บัญชี"
                            required
                        />
                        @error('label')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="sort_order" class="mb-2.5 block font-medium text-black dark:text-white">
                            ลำดับ
                        </label>
                        <input
                            type="number"
                            id="sort_order"
                            name="sort_order"
                            value="{{ old('sort_order', $group->sort_order) }}"
                            class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                        />
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="flex cursor-pointer">
                            <div class="relative">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', $group->is_active) ? 'checked' : '' }}
                                    class="sr-only"
                                />
                                <div class="block h-6 w-10 rounded-full bg-meta-9 dark:bg-[#5A616B]"></div>
                                <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition"></div>
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="block font-medium text-black dark:text-white">
                                    เปิดใช้งาน
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('admin.menu-groups.index') }}" class="inline-flex items-center justify-center rounded-md border border-stroke px-6 py-3 text-center font-medium text-black hover:bg-gray-50 dark:border-strokedark dark:text-white dark:hover:bg-gray-800">
                            ยกเลิก
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-primary px-6 py-3 text-center font-medium text-white hover:bg-opacity-90">
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection