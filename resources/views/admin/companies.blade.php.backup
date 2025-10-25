@extends('tailadmin.layouts.app')

@section('title', 'จัดการบริษัท')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">จัดการบริษัท</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-black dark:text-white" href="{{ route('dashboard') }}">Dashboard /</a></li>
                <li class="font-medium text-primary">จัดการบริษัท</li>
            </ol>
        </nav>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-lg border-l-4 border-green-500 bg-green-50 p-4 dark:bg-green-900/20">
            <p class="text-sm text-green-800 dark:text-green-400">{{ session('status') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white" id="form-title">เพิ่มบริษัทใหม่</h3>
                </div>
                <form id="company-form" method="POST" action="{{ route('admin.companies.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="form-method">
                    <input type="hidden" name="company_id" id="company-id">
                    <div class="p-6.5 space-y-4">
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Key <span class="text-meta-1">*</span></label>
                            <input type="text" name="key" id="key" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="เช่น default, JUNE, KSIIB" />
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">ชื่อบริษัท <span class="text-meta-1">*</span></label>
                            <input type="text" name="label" id="label" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="ชื่อบริษัทภาษาไทย" />
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Database Driver <span class="text-meta-1">*</span></label>
                            <select name="driver" id="driver" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary">
                                <option value="sqlsrv">SQL Server (sqlsrv)</option>
                                <option value="mysql">MySQL (mysql)</option>
                                <option value="pgsql">PostgreSQL (pgsql)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Host <span class="text-meta-1">*</span></label>
                            <input type="text" name="host" id="host" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="127.0.0.1 หรือ server.domain.com" />
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Port <span class="text-meta-1">*</span></label>
                            <input type="number" name="port" id="port" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="1433 / 3306 / 5432" />
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Database <span class="text-meta-1">*</span></label>
                            <input type="text" name="database" id="database" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="ชื่อ Database" />
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Username <span class="text-meta-1">*</span></label>
                            <input type="text" name="username" id="username" required class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="Database username" />
                        </div>
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Password <span class="text-meta-1" id="password-required">*</span></label>
                            <input type="password" name="password" id="password" class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="Database password" />
                            <p class="mt-1 text-xs text-meta-3 dark:text-meta-4" id="password-hint" style="display:none;">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</p>
                        </div>
                        <div x-data="{ open: false }" class="border-t border-stroke pt-4 dark:border-strokedark">
                            <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-sm font-medium text-black dark:text-white">
                                <span>ตัวเลือกขั้นสูง</span>
                                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="mt-4 space-y-4">
                                <div>
                                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Charset</label>
                                    <input type="text" name="charset" id="charset" class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="utf8, utf8mb4" />
                                </div>
                                <div>
                                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">Collation</label>
                                    <input type="text" name="collation" id="collation" class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="utf8_general_ci" />
                                </div>
                                <div>
                                    <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">ลำดับการแสดงผล</label>
                                    <input type="number" name="sort_order" id="sort_order" class="w-full rounded border-[1.5px] border-stroke bg-transparent px-5 py-3 font-medium outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary" placeholder="0" value="0" />
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 rounded bg-primary px-5 py-3 font-medium text-white hover:bg-opacity-90"><span id="submit-text">บันทึก</span></button>
                            <button type="button" onclick="resetForm()" class="rounded border border-stroke px-5 py-3 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">ยกเลิก</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="lg:col-span-7">
            <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
                <div class="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
                    <h3 class="font-medium text-black dark:text-white">รายการบริษัททั้งหมด ({{ $companies->count() }})</h3>
                </div>
                <div class="p-6.5">
                    @if($companies->isEmpty())
                        <p class="text-center text-sm text-bodydark dark:text-bodydark1">ยังไม่มีข้อมูลบริษัท</p>
                    @else
                        <div class="space-y-3">
                            @foreach($companies as $company)
                                <div class="rounded-lg border border-stroke p-4 dark:border-strokedark hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h4 class="font-semibold text-black dark:text-white">{{ $company->label }}</h4>
                                                @if($company->is_active)
                                                    <span class="inline-flex items-center rounded-full bg-success px-2.5 py-0.5 text-xs font-medium text-white">เปิดใช้งาน</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-meta-1 px-2.5 py-0.5 text-xs font-medium text-white">ปิดใช้งาน</span>
                                                @endif
                                            </div>
                                            <div class="space-y-1 text-sm text-bodydark dark:text-bodydark1">
                                                <p><span class="font-medium">Key:</span> {{ $company->key }}</p>
                                                <p><span class="font-medium">Server:</span> {{ $company->driver }}://{{ $company->host }}:{{ $company->port }}</p>
                                                <p><span class="font-medium">Database:</span> {{ $company->database }}</p>
                                                <p><span class="font-medium">Username:</span> {{ $company->username }}</p>
                                                @if($company->sort_order > 0)
                                                    <p><span class="font-medium">ลำดับ:</span> {{ $company->sort_order }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-2 ml-4">
                                            <button type="button" onclick='editCompany(@json($company))' class="inline-flex items-center justify-center rounded bg-meta-5 px-3 py-1.5 text-xs font-medium text-white hover:bg-opacity-90">แก้ไข</button>
                                            <form method="POST" action="{{ route('admin.companies.toggle', $company->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full inline-flex items-center justify-center rounded {{ $company->is_active ? 'bg-warning' : 'bg-success' }} px-3 py-1.5 text-xs font-medium text-white hover:bg-opacity-90">{{ $company->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.companies.test', $company->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="w-full inline-flex items-center justify-center rounded bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-opacity-90">ทดสอบการเชื่อมต่อ</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.companies.destroy', $company->id) }}" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบริษัท {{ $company->label }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full inline-flex items-center justify-center rounded bg-meta-1 px-3 py-1.5 text-xs font-medium text-white hover:bg-opacity-90">ลบ</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function editCompany(company) {
    document.getElementById('form-title').textContent = 'แก้ไขบริษัท';
    document.getElementById('submit-text').textContent = 'อัปเดต';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('company-form').action = `/admin/companies/${company.id}`;
    document.getElementById('company-id').value = company.id;
    document.getElementById('key').value = company.key;
    document.getElementById('label').value = company.label;
    document.getElementById('driver').value = company.driver;
    document.getElementById('host').value = company.host;
    document.getElementById('port').value = company.port;
    document.getElementById('database').value = company.database;
    document.getElementById('username').value = company.username;
    document.getElementById('charset').value = company.charset || '';
    document.getElementById('collation').value = company.collation || '';
    document.getElementById('sort_order').value = company.sort_order || 0;
    document.getElementById('password').removeAttribute('required');
    document.getElementById('password-required').style.display = 'none';
    document.getElementById('password-hint').style.display = 'block';
    document.getElementById('password').value = '';
    document.getElementById('company-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
function resetForm() {
    document.getElementById('form-title').textContent = 'เพิ่มบริษัทใหม่';
    document.getElementById('submit-text').textContent = 'บันทึก';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('company-form').action = '{{ route("admin.companies.store") }}';
    document.getElementById('company-form').reset();
    document.getElementById('password').setAttribute('required', 'required');
    document.getElementById('password-required').style.display = 'inline';
    document.getElementById('password-hint').style.display = 'none';
}
</script>
@endsection