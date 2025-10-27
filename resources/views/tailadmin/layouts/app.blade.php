<!doctype html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name') . ' - Admin Dashboard')</title>

    <!-- TailAdmin CSS (use original path from tailadmin folder) -->
    <link rel="icon" href="{{ asset('tailadmin-assets/images/favicon.ico') }}">
    <link href="{{ asset('tailadmin/style.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    @stack('styles')
</head>
<body
    x-data="{
        page: '{{ $page ?? 'dashboard' }}',
        loaded: true,
        darkMode: false,
        stickyMenu: false,
        sidebarToggle: false,
        scrollTop: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
>
    <!-- ===== Preloader Start ===== -->
    <div
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('tailadmin.partials.sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- ===== Header Start ===== -->
            @include('tailadmin.partials.header')
            <!-- ===== Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main>
                @yield('content')
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    <!-- Company Switcher Modal -->
    <div id="companySwitcherModal" style="display: none;" class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">เลือกบริษัท</h3>
                <button onclick="companySwitcher.closeModal()" class="text-white/90 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="companiesList" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- Companies will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    {{-- TailAdmin Bundle JS is commented out to prevent Alpine.js conflict with Vite's app.js --}}
    {{-- <script src="{{ asset('tailadmin/bundle.js') }}"></script> --}}

    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    @if (session('forbidden'))
        <script>
            window.addEventListener('DOMContentLoaded', function(){
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่มีสิทธิ์เข้าถึง',
                    text: @json(session('forbidden')),
                    confirmButtonColor: '#ef4444'
                });
            });
        </script>
    @endif

    <!-- Company Switcher Script -->
    <script>
    const companySwitcher = {
        currentCompanyId: null,
        companies: [],

        async openModal() {
            document.getElementById('companySwitcherModal').style.display = 'flex';
            await this.loadCompanies();
        },

        closeModal() {
            document.getElementById('companySwitcherModal').style.display = 'none';
        },

        async loadCompanies() {
            try {
                const response = await fetch('/admin/companies/accessible', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.companies = data.companies;
                    this.currentCompanyId = data.current_company_id;
                    this.renderCompanies();
                }
            } catch (error) {
                console.error('Error loading companies:', error);
            }
        },

        renderCompanies() {
            const container = document.getElementById('companiesList');

            if (this.companies.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        ไม่มีบริษัทที่สามารถเข้าถึงได้
                    </div>
                `;
                return;
            }

            container.innerHTML = this.companies.map(company => `
                <button
                    onclick="companySwitcher.switchTo(${company.id})"
                    class="w-full flex items-center gap-3 rounded-lg border p-4 transition ${
                        company.id === this.currentCompanyId
                            ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20'
                            : 'border-gray-200 dark:border-gray-700 hover:border-brand-300 hover:bg-gray-50 dark:hover:bg-gray-800'
                    }"
                >
                    <svg class="w-10 h-10 ${
                        company.id === this.currentCompanyId
                            ? 'text-brand-600 dark:text-brand-400'
                            : 'text-gray-400'
                    }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div class="flex-1 text-left">
                        <div class="font-semibold text-gray-900 dark:text-white">${company.label}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">${company.key}</div>
                    </div>
                    ${company.id === this.currentCompanyId ? `
                        <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    ` : ''}
                </button>
            `).join('');
        },

        async switchTo(companyId) {
            if (companyId === this.currentCompanyId) {
                this.closeModal();
                return;
            }

            try {
                const response = await fetch('/admin/companies/switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ company_id: companyId }),
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page to refresh with new company context
                            window.location.reload();
                        });
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: data.message
                        });
                    } else {
                        alert(data.message);
                    }
                }
            } catch (error) {
                console.error('Error switching company:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถสลับบริษัทได้'
                    });
                } else {
                    alert('ไม่สามารถสลับบริษัทได้');
                }
            }
        }
    };
    </script>

    @stack('scripts')
</body>
</html>
