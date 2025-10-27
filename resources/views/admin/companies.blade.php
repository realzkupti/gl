@extends('tailadmin.layouts.app')

@section('title', 'จัดการบริษัท')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-black dark:text-white">จัดการบริษัท</h2>
        <button type="button" onclick="companyManager.openAddModal()" class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-center font-medium text-white hover:bg-opacity-90">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            เพิ่มบริษัทใหม่
        </button>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-20 right-4 z-[9999] hidden">
        <div class="rounded-lg shadow-lg p-4 max-w-sm">
            <div class="flex items-center gap-3">
                <svg id="toast-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                <span id="toast-message" class="text-sm font-medium"></span>
            </div>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="px-6 py-6 overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Logo</th>
                        <th class="min-w-[150px] px-4 py-4 font-medium text-black dark:text-white">Key / ชื่อบริษัท</th>
                        <th class="min-w-[180px] px-4 py-4 font-medium text-black dark:text-white">Database</th>
                        <th class="min-w-[200px] px-4 py-4 font-medium text-black dark:text-white">Server</th>
                        <th class="px-4 py-4 text-center font-medium text-black dark:text-white">สถานะ</th>
                        <th class="px-4 py-4 text-center font-medium text-black dark:text-white">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="companies-tbody">
                    @forelse($companies as $company)
                    <tr class="border-b border-stroke dark:border-strokedark hover:bg-gray-50 dark:hover:bg-meta-4" data-id="{{ $company->id }}">
                        <td class="px-4 py-5">
                            @if($company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->label }}" class="w-12 h-12 object-contain rounded" />
                            @else
                                <div class="w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-5">
                            <div>
                                <p class="text-sm font-medium text-black dark:text-white">{{ $company->label }}</p>
                                <p class="text-xs text-meta-3">{{ $company->key }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            <div>
                                <p class="text-sm text-black dark:text-white">{{ $company->database }}</p>
                                <p class="text-xs text-meta-3">User: {{ $company->username }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-5">
                            <div>
                                <p class="text-sm text-black dark:text-white">{{ $company->host }}:{{ $company->port }}</p>
                                <p class="text-xs text-meta-3">{{ $company->driver }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-5 text-center">
                            @if($company->is_active)
                                <span class="inline-flex rounded-full bg-success bg-opacity-10 px-3 py-1 text-sm font-medium text-success">เปิดใช้งาน</span>
                            @else
                                <span class="inline-flex rounded-full bg-danger bg-opacity-10 px-3 py-1 text-sm font-medium text-danger">ปิดใช้งาน</span>
                            @endif
                        </td>
                        <td class="px-4 py-5">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" onclick='companyManager.editCompany(@json($company))' class="hover:text-primary" title="แก้ไข">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="companyManager.toggleActive({{ $company->id }})" class="hover:text-warning" title="{{ $company->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="companyManager.testConnection({{ $company->id }})" class="hover:text-success" title="ทดสอบการเชื่อมต่อ">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                                <button type="button" onclick="companyManager.deleteCompany({{ $company->id }}, '{{ $company->label }}')" class="hover:text-danger" title="ลบ">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <p class="text-meta-3">ยังไม่มีข้อมูลบริษัท</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="company-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white dark:bg-boxdark">
        <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
            <h3 class="text-lg font-medium text-black dark:text-white" id="modal-title">เพิ่มบริษัทใหม่</h3>
        </div>
        <form id="company-form" onsubmit="return companyManager.submitForm(event)">
            <input type="hidden" id="company-id" value="">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">Key <span class="text-meta-1">*</span></label>
                        <input type="text" name="key" id="key" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" placeholder="เช่น default, JUNE" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">ชื่อบริษัท <span class="text-meta-1">*</span></label>
                        <input type="text" name="label" id="label" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" placeholder="ชื่อบริษัทภาษาไทย" />
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">โลโก้บริษัท</label>
                    <div class="flex items-center gap-4">
                        <div id="logo-preview" class="hidden w-20 h-20 rounded border border-stroke p-2 bg-white dark:bg-boxdark">
                            <img id="logo-preview-img" src="" alt="Logo Preview" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" id="logo" accept="image/*" class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" onchange="companyManager.previewLogo(this)" />
                            <p class="mt-1 text-xs text-meta-3">รองรับ: JPG, PNG, GIF (ขนาดไม่เกิน 2MB)</p>
                            <input type="hidden" name="remove_logo" id="remove-logo" value="0" />
                        </div>
                        <button type="button" id="remove-logo-btn" class="hidden rounded border border-danger bg-danger bg-opacity-10 px-4 py-2 text-sm font-medium text-danger hover:bg-opacity-20" onclick="companyManager.removeLogo()">ลบโลโก้</button>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">Driver <span class="text-meta-1">*</span></label>
                        <select name="driver" id="driver" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input">
                            <option value="sqlsrv">SQL Server</option>
                            <option value="mysql">MySQL</option>
                            <option value="pgsql">PostgreSQL</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">Host <span class="text-meta-1">*</span></label>
                        <input type="text" name="host" id="host" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" placeholder="127.0.0.1" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">Port <span class="text-meta-1">*</span></label>
                        <input type="number" name="port" id="port" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" placeholder="1433" />
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">Database <span class="text-meta-1">*</span></label>
                    <input type="text" name="database" id="database" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" placeholder="ชื่อ Database" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">Username <span class="text-meta-1">*</span></label>
                        <input type="text" name="username" id="username" required class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-black dark:text-white">Password <span class="text-meta-1" id="password-required">*</span></label>
                        <input type="password" name="password" id="password" class="w-full rounded border border-stroke bg-transparent px-4 py-3 outline-none focus:border-primary dark:border-form-strokedark dark:bg-form-input" />
                        <p class="mt-1 text-xs text-meta-3" id="password-hint" style="display:none;">เว้นว่างหากไม่เปลี่ยน</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-stroke px-6 py-4 dark:border-strokedark flex justify-end gap-3">
                <button type="button" onclick="companyManager.closeModal()" class="rounded border border-stroke px-6 py-2 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white">ยกเลิก</button>
                <button type="submit" class="rounded bg-primary px-6 py-2 font-medium text-white hover:bg-opacity-90" id="submit-btn">บันทึก</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const companyManager = {
    companies: @json($companies),

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toast-icon');
        const messageEl = document.getElementById('toast-message');
        const toastDiv = toast.querySelector('div');

        messageEl.textContent = message;

        if (type === 'success') {
            toastDiv.className = 'rounded-lg shadow-lg p-4 max-w-sm bg-green-500 text-white';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        } else {
            toastDiv.className = 'rounded-lg shadow-lg p-4 max-w-sm bg-red-500 text-white';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        }

        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    },

    previewLogo(input) {
        const preview = document.getElementById('logo-preview');
        const previewImg = document.getElementById('logo-preview-img');
        const removeBtn = document.getElementById('remove-logo-btn');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
                removeBtn.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    },

    removeLogo() {
        document.getElementById('logo').value = '';
        document.getElementById('logo-preview').classList.add('hidden');
        document.getElementById('remove-logo-btn').classList.add('hidden');
        document.getElementById('remove-logo').value = '1';
    },

    openAddModal() {
        document.getElementById('modal-title').textContent = 'เพิ่มบริษัทใหม่';
        document.getElementById('company-form').reset();
        document.getElementById('company-id').value = '';
        document.getElementById('password').setAttribute('required', 'required');
        document.getElementById('password-required').style.display = 'inline';
        document.getElementById('password-hint').style.display = 'none';
        document.getElementById('logo-preview').classList.add('hidden');
        document.getElementById('remove-logo-btn').classList.add('hidden');
        document.getElementById('remove-logo').value = '0';
        document.getElementById('company-modal').classList.remove('hidden');
        document.getElementById('company-modal').classList.add('flex');
    },

    editCompany(company) {
        console.log('Editing company:', company);
        document.getElementById('modal-title').textContent = 'แก้ไขบริษัท';
        document.getElementById('company-id').value = company.id;
        document.getElementById('key').value = company.key;
        document.getElementById('label').value = company.label;
        document.getElementById('driver').value = company.driver;
        document.getElementById('host').value = company.host;
        document.getElementById('port').value = company.port;
        document.getElementById('database').value = company.database;
        document.getElementById('username').value = company.username;
        document.getElementById('password').removeAttribute('required');
        document.getElementById('password-required').style.display = 'none';
        document.getElementById('password-hint').style.display = 'block';
        document.getElementById('password').value = '';
        document.getElementById('remove-logo').value = '0';

        // Show logo preview if exists
        const logoPreview = document.getElementById('logo-preview');
        const logoPreviewImg = document.getElementById('logo-preview-img');
        const removeBtn = document.getElementById('remove-logo-btn');

        if (company.logo) {
            logoPreviewImg.src = '/storage/' + company.logo;
            logoPreview.classList.remove('hidden');
            removeBtn.classList.remove('hidden');
        } else {
            logoPreview.classList.add('hidden');
            removeBtn.classList.add('hidden');
        }

        document.getElementById('company-modal').classList.remove('hidden');
        document.getElementById('company-modal').classList.add('flex');
    },

    closeModal() {
        document.getElementById('company-modal').classList.add('hidden');
        document.getElementById('company-modal').classList.remove('flex');
    },

    async submitForm(event) {
        event.preventDefault();

        const companyId = document.getElementById('company-id').value;
        const isEdit = companyId !== '';
        const formData = new FormData(event.target);

        const url = isEdit ? `/admin/companies/${companyId}` : '/admin/companies';
        const method = 'POST';

        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(isEdit ? 'อัปเดตบริษัทเรียบร้อยแล้ว' : 'เพิ่มบริษัทเรียบร้อยแล้ว', 'success');
                this.closeModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                console.error('Error:', data);
                this.showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Submit error:', error);
            this.showToast('เกิดข้อผิดพลาด: ' + error.message, 'error');
        }

        return false;
    },

    async toggleActive(companyId) {
        try {
            const response = await fetch(`/admin/companies/${companyId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                console.error('Error:', data);
                this.showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Toggle error:', error);
            this.showToast('เกิดข้อผิดพลาด: ' + error.message, 'error');
        }
    },

    async testConnection(companyId) {
        console.log('Testing connection for company:', companyId);

        try {
            const response = await fetch(`/admin/companies/${companyId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Test connection response:', data);

            if (data.success) {
                this.showToast(data.message, 'success');
            } else {
                this.showToast(data.message || 'เชื่อมต่อไม่สำเร็จ', 'error');
            }
        } catch (error) {
            console.error('Test connection error:', error);
            this.showToast('เกิดข้อผิดพลาด: ' + error.message, 'error');
        }
    },

    async deleteCompany(companyId, companyLabel) {
        if (!confirm(`คุณแน่ใจหรือไม่ว่าต้องการลบบริษัท ${companyLabel}?`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/companies/${companyId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('ลบบริษัทเรียบร้อยแล้ว', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                console.error('Error:', data);
                this.showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showToast('เกิดข้อผิดพลาด: ' + error.message, 'error');
        }
    }
};
</script>
@endpush
@endsection
