@props(['companies' => []])

<!-- Company Switch Modal -->
<div id="company-switch-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">เลือกบริษัทx</h3>
                <button onclick="closeCompanySwitchModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4 max-h-96 overflow-y-auto">
            @if(count($companies) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($companies as $company)
                    <div onclick="switchCompany({{ $company->id }})"
                         class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition {{ session('current_company_id') == $company->id ? 'bg-brand-50 dark:bg-brand-900/20 border-brand-500' : '' }}">
                        <!-- Logo -->
                        @if($company->logo)
                            <img src="{{ asset('storage/' . $company->logo) }}"
                                 alt="{{ $company->label }}"
                                 class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        @endif

                        <!-- Info -->
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $company->label }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $company->key }}</div>
                        </div>

                        <!-- Active Badge -->
                        @if(session('current_company_id') == $company->id)
                            <div class="flex items-center gap-1 text-brand-600 dark:text-brand-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p>ไม่พบบริษัทที่คุณสามารถเข้าถึงได้</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button onclick="closeCompanySwitchModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                ปิด
            </button>
        </div>
    </div>
</div>

<script>
function openCompanySwitchModal() {
    console.log('Opening company switch modal');
    document.getElementById('company-switch-modal').classList.remove('hidden');
}

function closeCompanySwitchModal() {
    console.log('Closing company switch modal');
    document.getElementById('company-switch-modal').classList.add('hidden');
}

function switchCompany(companyId) {
    // Close the company switch modal first
    console.log('Switching to company ID:', companyId);
    closeCompanySwitchModal();

    // Show SweetAlert2 loading
    Swal.fire({
        title: 'กำลังเชื่อมต่อฐานข้อมูล...',
        html: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Set timeout for connection test (10 seconds)
    const timeoutId = setTimeout(() => {
        Swal.fire({
            icon: 'error',
            title: 'หมดเวลาการเชื่อมต่อ',
            html: 'ใช้เวลาเชื่อมต่อนานเกินไป<br><small class="text-gray-500">กรุณาตรวจสอบการเชื่อมต่อเครือข่ายและลองใหม่อีกครั้ง</small>',
            confirmButtonText: 'ตรวจสอบอีกครั้ง',
            showCancelButton: true,
            cancelButtonText: 'ปิด'
        }).then((result) => {
            if (result.isConfirmed) {
                switchCompany(companyId);
            }
        });
    }, 10000); // 10 seconds timeout

    // Switch company
    fetch('/admin/companies/switch', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            company_id: companyId
        })
    })
    .then(response => {
        clearTimeout(timeoutId);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
                willClose: () => {
                    window.location.reload();
                }
            });
        } else {
            // Show error message
            showConnectionErrorSwal(data);
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        console.error('Error:', error);
        showConnectionErrorSwal({
            message: 'เกิดข้อผิดพลาดในการเปลี่ยนบริษัท',
            error: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'
        });
    });
}

function showConnectionErrorSwal(data) {
    const technicalDetails = data.technical_details ?
        `<div class="mt-3 p-3 bg-gray-100 rounded text-xs text-left text-gray-600 overflow-auto" style="max-height: 120px;">
            <strong>Technical Details:</strong><br>
            ${data.technical_details}
        </div>` : '';

    Swal.fire({
        icon: 'error',
        title: 'การเชื่อมต่อล้มเหลว',
        html: `
            <div class="text-left">
                <p class="text-base font-semibold text-gray-900 mb-2">
                    ${data.message || 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'}
                </p>
                <p class="text-sm text-red-600 mb-3">
                    ${data.error || 'กรุณาตรวจสอบการตั้งค่าการเชื่อมต่อฐานข้อมูล'}
                </p>
                ${technicalDetails}
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800 mb-2">
                        <strong>คำแนะนำ:</strong> กรุณาติดต่อผู้ดูแลระบบเพื่อตรวจสอบ:
                    </p>
                    <ul class="ml-4 text-sm text-yellow-700 list-disc space-y-1">
                        <li>การตั้งค่า Host และ Port ของฐานข้อมูล</li>
                        <li>ชื่อผู้ใช้และรหัสผ่านที่ถูกต้อง</li>
                        <li>ฐานข้อมูลยังคงใช้งานได้ปกติ</li>
                        <li>Firewall ไม่ได้บล็อกการเชื่อมต่อ</li>
                    </ul>
                </div>
            </div>
        `,
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#3085d6',
        width: '600px'
    });
}

// Close modal on background click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('company-switch-modal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCompanySwitchModal();
            }
        });
    }
});
</script>
