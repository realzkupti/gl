@extends('layouts.admin')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">ผู้ใช้และสิทธิ (ตัวอย่างข้อมูล)</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="p-4 border rounded bg-white">
            <h2 class="text-lg font-semibold mb-3">ผู้ใช้</h2>
            <table class="min-w-full border-collapse w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-1">ID</th>
                        <th class="border px-2 py-1">ชื่อ</th>
                        <th class="border px-2 py-1">อีเมล</th>
                        <th class="border px-2 py-1">บทบาท</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td class="border px-2 py-1">{{ $u['id'] }}</td>
                            <td class="border px-2 py-1">{{ $u['name'] }}</td>
                            <td class="border px-2 py-1">{{ $u['email'] }}</td>
                            <td class="border px-2 py-1">{{ implode(', ', $u['roles']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                <h3 class="font-semibold">เพิ่มผู้ใช้ (Mock)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <input type="text" class="border rounded px-2 py-1" placeholder="ชื่อ">
                    <input type="email" class="border rounded px-2 py-1" placeholder="อีเมล">
                    <input type="password" class="border rounded px-2 py-1" placeholder="รหัสผ่าน">
                    <select class="border rounded px-2 py-1">
                        @foreach($roles as $r)
                            <option value="{{ $r['name'] }}">{{ $r['name'] }} ({{ $r['description'] }})</option>
                        @endforeach
                    </select>
                </div>
                <button class="mt-2 px-3 py-1 bg-blue-600 text-white rounded" onclick="Swal && Swal.fire('ตัวอย่าง','ยังไม่เชื่อม DB','info')">บันทึก</button>
            </div>
        </div>

        <div class="p-4 border rounded bg-white">
            <h2 class="text-lg font-semibold mb-3">บทบาท</h2>
            <ul class="list-disc list-inside">
                @foreach($roles as $r)
                    <li><strong>{{ $r['name'] }}</strong> — {{ $r['description'] }}</li>
                @endforeach
            </ul>

            <div class="mt-4">
                <h3 class="font-semibold">เพิ่มบทบาท (Mock)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <input type="text" class="border rounded px-2 py-1" placeholder="ชื่อบทบาท (อังกฤษ)">
                    <input type="text" class="border rounded px-2 py-1" placeholder="คำอธิบาย">
                </div>
                <button class="mt-2 px-3 py-1 bg-blue-600 text-white rounded" onclick="Swal && Swal.fire('ตัวอย่าง','ยังไม่เชื่อม DB','info')">บันทึก</button>
            </div>
        </div>
    </div>

    <div class="mt-6 p-4 border rounded bg-white">
        <h2 class="text-lg font-semibold mb-3">สิทธิรายเมนู (Mock)</h2>
        <div class="overflow-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-1">บทบาท \ เมนู</th>
                        @foreach($menus as $m)
                            <th class="border px-2 py-1">{{ $m['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $r)
                        <tr>
                            <td class="border px-2 py-1 font-semibold">{{ $r['name'] }}</td>
                            @foreach($menus as $m)
                                @php $perm = $permissions[$r['name']][$m['key']] ?? ['view'=>false]; @endphp
                                <td class="border px-2 py-1">
                                    <label class="inline-flex items-center gap-1 mr-2"><input type="checkbox" {{ $perm['view'] ? 'checked' : '' }}> ดู</label>
                                    <label class="inline-flex items-center gap-1 mr-2"><input type="checkbox" {{ ($perm['create'] ?? false) ? 'checked' : '' }}> เพิ่ม</label>
                                    <label class="inline-flex items-center gap-1 mr-2"><input type="checkbox" {{ ($perm['update'] ?? false) ? 'checked' : '' }}> แก้ไข</label>
                                    <label class="inline-flex items-center gap-1 mr-2"><input type="checkbox" {{ ($perm['delete'] ?? false) ? 'checked' : '' }}> ลบ</label>
                                    <label class="inline-flex items-center gap-1 mr-2"><input type="checkbox" {{ ($perm['export'] ?? false) ? 'checked' : '' }}> ส่งออก</label>
                                    <label class="inline-flex items-center gap-1"><input type="checkbox" {{ ($perm['approve'] ?? false) ? 'checked' : '' }}> อนุมัติ</label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button class="mt-3 px-3 py-1 bg-green-600 text-white rounded" onclick="Swal && Swal.fire('ตัวอย่าง','ยังไม่เชื่อม DB','info')">บันทึกสิทธิ</button>
    </div>
</div>
@endsection

