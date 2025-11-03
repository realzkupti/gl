@extends('tailadmin.layouts.app')

@section('title', 'จัดการผู้ใช้')

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">จัดการผู้ใช้</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">จัดการผู้ใช้ในระบบ, แผนก, และสิทธิ์การเข้าถึงบริษัท</p>
    </div>

    @if(session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="flex space-x-4" aria-label="Tabs">
            <button type="button" onclick="switchTab('active')" data-tab="active"
               class="tab-button px-4 py-2 border-b-2 font-medium text-sm {{ $tab === 'active' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                ผู้ใช้ที่ใช้งานอยู่ (<span id="active-count">{{ $users->where('is_active', true)->count() }}</span>)
            </button>
            <button type="button" onclick="switchTab('pending')" data-tab="pending"
               class="tab-button px-4 py-2 border-b-2 font-medium text-sm {{ $tab === 'pending' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                รออนุมัติ (<span id="pending-count">{{ $users->where('is_active', false)->count() }}</span>)
            </button>
        </nav>
    </div>

    <!-- Add User Button -->
    <div class="mb-4">
        <button onclick="openAddUserModal()" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition">
            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            เพิ่มผู้ใช้ใหม่
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ผู้ใช้</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">แผนก</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">บริษัทที่เข้าถึง</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">สถานะ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="user-row hover:bg-gray-50 dark:hover:bg-gray-700" data-is-active="{{ $user->is_active ? '1' : '0' }}" data-user-id="{{ $user->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center">
                                            <span class="text-brand-700 dark:text-brand-300 font-medium">{{ $user->initials() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $user->department->label ?? 'ไม่ระบุ' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    @if($user->companies->count() > 0)
                                        @foreach($user->companies->take(2) as $company)
                                            <span class="inline-block px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded mr-1 mb-1">{{ $company->label }}</span>
                                        @endforeach
                                        @if($user->companies->count() > 2)
                                            <span class="text-xs text-gray-500">+{{ $user->companies->count() - 2 }} อื่นๆ</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">ไม่มี</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        ใช้งาน
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        รออนุมัติ
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($tab === 'pending')
                                    <!-- Pending user actions -->
                                    <button onclick="approveUser({{ $user->id }}, '{{ $user->name }}')" class="text-green-600 hover:text-green-900 dark:hover:text-green-400 mr-3">อนุมัติ</button>
                                    <button onclick="rejectUser({{ $user->id }}, '{{ $user->name }}')" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">ปฏิเสธ</button>
                                @else
                                    <!-- Active user actions -->
                                    <button onclick="openEditUserModal({{ $user->id }}, {{ json_encode($user) }})" class="text-brand-600 hover:text-brand-900 dark:hover:text-brand-400 mr-3">แก้ไข</button>
                                    <a href="{{ route('admin.user-permissions.edit', $user->id) }}" class="text-purple-600 hover:text-purple-900 dark:hover:text-purple-400 mr-3">สิทธิ์</a>
                                    @if($user->email !== 'admin@local')
                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">ลบ</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                @if($tab === 'pending')
                                    ไม่มีผู้ใช้ที่รออนุมัติ
                                @else
                                    ไม่มีผู้ใช้
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" style="display: none;" class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-4 flex items-center justify-between">
            <h3 id="modalTitle" class="text-lg font-semibold text-white">เพิ่มผู้ใช้ใหม่</h3>
            <button onclick="closeUserModal()" class="text-white/90 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ชื่อ</label>
                    <input type="text" name="name" id="userName" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">อีเมล</label>
                    <input type="email" name="email" id="userEmail" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">รหัสผ่าน <span id="passwordOptional" class="text-xs text-gray-500">(เว้นว่างหากไม่เปลี่ยน)</span></label>
                    <input type="password" name="password" id="userPassword" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">แผนก</label>
                    <select name="department_id" id="userDepartment" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:text-white">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">บริษัทที่เข้าถึง (เลือกได้หลายบริษัท)</label>
                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-3 max-h-48 overflow-y-auto">
                    @foreach($companies as $company)
                        <label class="flex items-center py-2 hover:bg-gray-50 dark:hover:bg-gray-800 px-2 rounded">
                            <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" class="userCompanyCheckbox mr-2 rounded text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $company->label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" id="userIsActive" value="1" checked class="rounded text-brand-600 focus:ring-brand-500 mr-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">เปิดใช้งานทันที</span>
                </label>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeUserModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    ยกเลิก
                </button>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition shadow-md hover:shadow-lg">
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .userCompanyCheckbox:checked + span {
        font-weight: 600;
    }
</style>
@endpush

<script>
function openAddUserModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มผู้ใช้ใหม่';
    document.getElementById('userForm').action = '{{ route("admin.users.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('userForm').reset();
    document.getElementById('passwordOptional').style.display = 'none';
    document.getElementById('userPassword').required = true;
    document.querySelectorAll('.userCompanyCheckbox').forEach(cb => cb.checked = false);
    document.getElementById('userModal').style.display = 'flex';
}

function openEditUserModal(userId, user) {
    document.getElementById('modalTitle').textContent = 'แก้ไขผู้ใช้';
    document.getElementById('userForm').action = `/admin/users/${userId}`;
    document.getElementById('formMethod').value = 'PUT';

    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('userDepartment').value = user.department_id;
    document.getElementById('userIsActive').checked = user.is_active;

    document.getElementById('passwordOptional').style.display = 'inline';

    // Set company checkboxes
    document.querySelectorAll('.userCompanyCheckbox').forEach(cb => cb.checked = false);
    if (user.companies) {
        user.companies.forEach(company => {
            const checkbox = document.querySelector(`.userCompanyCheckbox[value="${company.id}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }

    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Approve user
async function approveUser(userId, userName) {
    const result = await Swal.fire({
        title: 'ยืนยันการอนุมัติ',
        text: `คุณต้องการอนุมัติผู้ใช้ "${userName}" ใช่หรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'อนุมัติ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#10b981'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('{{ route('admin.users.approve', ':id') }}'.replace(':id', userId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });

            // Remove row from table
            document.querySelector(`tr[data-user-id="${userId}"]`)?.remove();

            // Reload counts
            loadUserCounts();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ผิดพลาด!',
                text: data.message || 'เกิดข้อผิดพลาด',
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        console.error('Error approving user:', error);
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด!',
            text: 'ไม่สามารถเชื่อมต่อกับ server ได้',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Reject user
async function rejectUser(userId, userName) {
    const result = await Swal.fire({
        title: 'ยืนยันการปฏิเสธ',
        text: `คุณต้องการปฏิเสธและลบผู้ใช้ "${userName}" ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ปฏิเสธและลบ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#ef4444'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('{{ route('admin.users.reject', ':id') }}'.replace(':id', userId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });

            // Remove row from table
            document.querySelector(`tr[data-user-id="${userId}"]`)?.remove();

            // Reload counts
            loadUserCounts();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ผิดพลาด!',
                text: data.message || 'เกิดข้อผิดพลาด',
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        console.error('Error rejecting user:', error);
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด!',
            text: 'ไม่สามารถเชื่อมต่อกับ server ได้',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Delete user
async function deleteUser(userId, userName) {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ',
        text: `คุณต้องการลบผู้ใช้ "${userName}" ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#ef4444'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('{{ route('admin.users.destroy', ':id') }}'.replace(':id', userId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });

            // Remove row from table
            document.querySelector(`tr[data-user-id="${userId}"]`)?.remove();

            // Reload counts
            loadUserCounts();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ผิดพลาด!',
                text: data.message || 'เกิดข้อผิดพลาด',
                confirmButtonColor: '#ef4444'
            });
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด!',
            text: 'ไม่สามารถเชื่อมต่อกับ server ได้',
            confirmButtonColor: '#ef4444'
        });
    }
}

// Load user counts on page load
function loadUserCounts() {
    fetch('{{ route('admin.users.counts') }}', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('active-count').textContent = data.active_count;
            document.getElementById('pending-count').textContent = data.pending_count;
        }
    })
    .catch(error => console.error('Error loading user counts:', error));
}

// Switch tab function - filter users without page reload
let currentTab = '{{ $tab }}';

function switchTab(tab) {
    if (currentTab === tab) return;

    currentTab = tab;

    // Update tab styles
    document.querySelectorAll('.tab-button').forEach(btn => {
        const isActive = btn.dataset.tab === tab;
        if (isActive) {
            btn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            btn.classList.add('border-brand-500', 'text-brand-600', 'dark:text-brand-400');
        } else {
            btn.classList.remove('border-brand-500', 'text-brand-600', 'dark:text-brand-400');
            btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }
    });

    // Filter user rows
    filterUserRows(tab);

    // Update URL without reload
    if (history.pushState) {
        const newUrl = `/admin/users?tab=${tab}`;
        window.history.pushState({ path: newUrl }, '', newUrl);
    }
}

function filterUserRows(tab) {
    const showActive = (tab === 'active');
    document.querySelectorAll('.user-row').forEach(row => {
        const isActive = row.dataset.isActive === '1';
        if (showActive) {
            // Show active users
            row.style.display = isActive ? '' : 'none';
        } else {
            // Show pending users
            row.style.display = isActive ? 'none' : '';
        }
    });
}

// Load counts and filter initial tab when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadUserCounts();
    // Apply initial filter based on current tab
    filterUserRows(currentTab);
});
</script>
@endsection
