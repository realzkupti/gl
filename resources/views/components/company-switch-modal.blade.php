@props(['companies' => []])

<!-- Company Switch Modal -->
<div id="company-switch-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">เลือกบริษัท</h3>
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
    document.getElementById('company-switch-modal').classList.remove('hidden');
}

function closeCompanySwitchModal() {
    document.getElementById('company-switch-modal').classList.add('hidden');
}

function switchCompany(companyId) {
    // Show loading
    const modal = document.getElementById('company-switch-modal');
    const originalContent = modal.innerHTML;
    modal.innerHTML = '<div class="flex items-center justify-center h-64"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500"></div></div>';

    // Switch company
    fetch(`/company/switch/${companyId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update sidebar
            window.location.reload();
        } else {
            alert(data.message || 'เกิดข้อผิดพลาด');
            modal.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเปลี่ยนบริษัท');
        modal.innerHTML = originalContent;
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
