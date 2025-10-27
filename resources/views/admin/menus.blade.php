@extends('tailadmin.layouts.app')

@section('title', 'จัดการเมนู - ' . config('app.name'))

@push('styles')
<style>
.menu-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.menu-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}
.sortable-ghost {
    opacity: 0.4;
    background: #f3f4f6;
}
.sortable-drag {
    opacity: 0.8;
    cursor: grabbing !important;
}
.drag-handle {
    cursor: grab;
}
.drag-handle:active {
    cursor: grabbing;
}
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10" id="menuManagerApp">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                จัดการเมนูระบบ
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                จัดการเมนูและการแสดงผลของระบบ สามารถลากเพื่อเรียงลำดับได้
            </p>
        </div>

        <button onclick="menuManager.openCreateModal()" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-5 py-3 text-white hover:bg-brand-700 transition shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="font-medium">เพิ่มเมนูใหม่</span>
        </button>
    </div>

    <!-- Toast Notification -->
    <div id="toast" style="display: none;" class="fixed top-4 right-4 z-50 max-w-sm rounded-lg shadow-lg">
        <div id="toastContent" class="p-4 text-white flex items-center gap-3">
            <svg id="toastIconSuccess" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg id="toastIconError" class="w-6 h-6" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span id="toastMessage"></span>
        </div>
    </div>

    <!-- System Type Groups -->
    <div class="space-y-6" id="systemTypeGroups"></div>

    <!-- Create/Edit Modal -->
    <div id="menuModal" style="display: none;" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white" id="modalTitle">เพิ่มเมนูใหม่</h3>
                <button onclick="menuManager.closeModal()" class="text-white/90 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form onsubmit="menuManager.submitForm(event)" class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                <div class="space-y-4">
                    <!-- Key -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Key <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="formKey" required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white"
                            placeholder="dashboard, reports, users" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">รหัสเมนูที่ไม่ซ้ำกัน (ภาษาอังกฤษ)</p>
                    </div>

                    <!-- Label -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                            ชื่อเมนู <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="formLabel" required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white"
                            placeholder="แดชบอร์ด, รายงาน, ผู้ใช้งาน" />
                    </div>

                    <!-- Route -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Route Name</label>
                        <input type="text" id="formRoute"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white"
                            placeholder="tailadmin.dashboard" />
                    </div>

                    <!-- Icon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Icon</label>
                        <div class="flex gap-2">
                            <input type="text" id="formIcon" readonly onclick="menuManager.openIconPicker()"
                                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-2.5 cursor-pointer dark:text-white"
                                placeholder="คลิกเพื่อเลือก icon" />
                            <button type="button" onclick="menuManager.openIconPicker()" class="rounded-lg bg-purple-600 px-4 py-2.5 text-white hover:bg-purple-700 transition">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            <button type="button" onclick="document.getElementById('formIcon').value = ''" class="rounded-lg bg-gray-400 px-4 py-2.5 text-white hover:bg-gray-500 transition">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Parent Menu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">ประเภทเมนู</label>
                        <select id="formParentId" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white">
                            <option value="">🔹 เมนูหลัก (Main Menu)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- System Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">ระบบ</label>
                            <select id="formSystemType" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white">
                                <option value="1">System</option>
                                <option value="2">Bplus</option>
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">ลำดับการแสดง</label>
                            <input type="number" id="formSortOrder"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white" />
                        </div>
                    </div>

                    <!-- Connection Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Connection Type</label>
                        <select id="formConnectionType" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:text-white">
                            <option value="pgsql">PostgreSQL (System)</option>
                            <option value="company">Company Database</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 flex justify-end gap-3">
                <button onclick="menuManager.closeModal()" type="button" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-2.5 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition">
                    ยกเลิก
                </button>
                <button onclick="menuManager.submitForm(event)" type="button" class="rounded-lg bg-brand-600 px-5 py-2.5 text-white hover:bg-brand-700 font-medium shadow-sm transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span id="modalSubmitText">สร้างเมนู</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <div id="iconPickerModal" style="display: none;" class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-white">เลือก Icon</h3>
                <button onclick="menuManager.closeIconPicker()" class="text-white hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-4">
                <input type="text" id="iconSearch" oninput="menuManager.filterIcons()" placeholder="ค้นหา icon..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-purple-500 focus:ring-2 focus:ring-purple-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>

            <div class="px-6 pb-6 overflow-y-auto" style="max-height: 60vh;" id="iconGrid"></div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none;" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gradient-to-r from-red-600 to-rose-600">
                <h3 class="text-white font-semibold">ยืนยันการลบเมนู</h3>
                <button onclick="menuManager.closeDeleteModal()" class="text-white/90 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="flex items-start gap-3 mb-4">
                    <div class="rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 p-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-900 dark:text-white font-medium">คุณต้องการลบเมนูนี้ใช่หรือไม่?</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">เมนู: <span class="font-semibold" id="deleteMenuLabel"></span></p>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button onclick="menuManager.closeDeleteModal()" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                        ยกเลิก
                    </button>
                    <button onclick="menuManager.deleteMenu()" class="rounded-lg px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 shadow-sm">
                        ยืนยันลบ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
const menuManager = {
    allMenus: @json($menus),
    systemTypes: @json($systemTypes),
    groupedMenus: {},
    modal: {
        open: false,
        mode: 'create',
    },
    form: {
        id: null,
        key: '',
        label: '',
        route: '',
        icon: '',
        parent_id: '',
        system_type: 1,
        sort_order: 0,
        connection_type: 'pgsql',
    },
    deleteModal: {
        open: false,
        menu: null,
    },
    sortableInstances: [],
    icons: [
        { name: 'home', path: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { name: 'dashboard', path: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
        { name: 'users', path: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
        { name: 'cog', path: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
        { name: 'chart', path: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
        { name: 'document', path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
        { name: 'folder', path: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' },
        { name: 'credit-card', path: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
        { name: 'calculator', path: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
        { name: 'lock', path: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
        { name: 'bell', path: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    ],

    init() {
        this.groupMenusBySystemType();
        this.renderGroups();
        setTimeout(() => this.initSortable(), 100);
    },

    groupMenusBySystemType() {
        const groups = {};

        this.systemTypes.forEach(type => {
            const parentMenus = this.allMenus.filter(m =>
                m.system_type == type.id && !m.parent_id
            );

            const menusWithChildren = parentMenus.map(parent => {
                const children = this.allMenus.filter(m => m.parent_id == parent.id);
                return {
                    ...parent,
                    children: children,
                    hasChildren: children.length > 0
                };
            });

            groups[type.id] = {
                id: type.id,
                key: type.key,
                label: type.label,
                menus: menusWithChildren,
                collapsed: false
            };
        });

        this.groupedMenus = groups;
    },

    renderGroups() {
        const container = document.getElementById('systemTypeGroups');
        container.innerHTML = '';

        Object.values(this.groupedMenus).forEach(group => {
            const groupEl = this.createGroupElement(group);
            container.appendChild(groupEl);
        });

        this.updateParentMenuSelect();
    },

    createGroupElement(group) {
        const div = document.createElement('div');
        div.className = 'rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden';
        div.innerHTML = `
            <div class="bg-gradient-to-r from-brand-600 to-brand-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-white">${group.label}</h3>
                    <span class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white">${group.menus.length} เมนู</span>
                </div>
                <button onclick="menuManager.toggleGroup(${group.id})" class="text-white hover:text-gray-200 transition">
                    <svg class="w-5 h-5 transition-transform ${group.collapsed ? '' : 'rotate-180'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
            <div id="group-${group.id}" class="p-6" style="display: ${group.collapsed ? 'none' : 'block'};">
                <div id="menu-list-${group.id}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${group.menus.map(menu => this.createMenuCard(menu)).join('')}
                </div>
                ${group.menus.length === 0 ? `
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">ยังไม่มีเมนูในกลุ่มนี้</p>
                    </div>
                ` : ''}
            </div>
        `;
        return div;
    },

    createMenuCard(menu) {
        const icon = this.icons.find(i => i.name === menu.icon);
        const iconPath = icon ? icon.path : 'M4 6h16M4 12h16M4 18h16';

        return `
            <div data-id="${menu.id}" data-parent="true" class="menu-card rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden cursor-move">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="drag-handle w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <span class="text-xs font-mono text-gray-500">#${menu.id}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full ${menu.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'} px-2 py-0.5 text-xs font-medium">
                            ${menu.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 dark:text-white truncate">${menu.label}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono truncate">${menu.key}</p>
                            <div class="mt-1 flex items-center gap-2 flex-wrap">
                                ${menu.hasChildren ? `
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                        </svg>
                                        ${menu.children.length} เมนูย่อย
                                    </span>
                                ` : ''}
                                <span class="text-xs text-gray-600 dark:text-gray-400">ลำดับ: ${menu.sort_order}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button onclick="menuManager.toggleActive(${menu.id})" class="flex-1 inline-flex items-center justify-center gap-2 rounded-md px-3 py-2 text-xs font-medium transition ${menu.is_active ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400'}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${menu.is_active ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'}"/>
                            </svg>
                            <span>${menu.is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน'}</span>
                        </button>
                        <button onclick="menuManager.openEditModal(${menu.id})" class="inline-flex items-center justify-center rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 px-3 py-2 text-xs font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        ${!menu.is_system ? `
                            <button onclick="menuManager.confirmDelete(${menu.id})" class="inline-flex items-center justify-center rounded-md bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 px-3 py-2 text-xs font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    },

    toggleGroup(groupId) {
        this.groupedMenus[groupId].collapsed = !this.groupedMenus[groupId].collapsed;
        this.renderGroups();
        setTimeout(() => this.initSortable(), 100);
    },

    initSortable() {
        this.sortableInstances.forEach(instance => instance.destroy());
        this.sortableInstances = [];

        Object.keys(this.groupedMenus).forEach(groupKey => {
            const el = document.getElementById(`menu-list-${groupKey}`);
            if (el) {
                const sortable = new Sortable(el, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: (evt) => {
                        this.updateSortOrder(groupKey);
                    }
                });
                this.sortableInstances.push(sortable);
            }
        });
    },

    async updateSortOrder(groupKey) {
        const el = document.getElementById(`menu-list-${groupKey}`);
        const cards = el.querySelectorAll('.menu-card[data-parent="true"]');
        const order = [];

        Array.from(cards).forEach((card, index) => {
            const menuId = parseInt(card.dataset.id);
            const menu = this.allMenus.find(m => m.id === menuId);

            order.push({
                id: menuId,
                sort_order: index
            });

            if (menu) {
                const children = this.allMenus.filter(m => m.parent_id === menuId);
                children.forEach((child, childIndex) => {
                    order.push({
                        id: child.id,
                        sort_order: index + (childIndex + 1) * 0.1
                    });
                });
            }
        });

        try {
            const response = await fetch('/admin/menus/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order })
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('อัพเดตลำดับเรียบร้อย', 'success');
                await this.loadMenus();
            }
        } catch (error) {
            console.error('Error updating sort order:', error);
            this.showToast('เกิดข้อผิดพลาดในการเรียงลำดับ', 'error');
        }
    },

    async loadMenus() {
        try {
            const response = await fetch('/admin/menus/list', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            const data = await response.json();

            if (data.success) {
                this.allMenus = data.data;
                this.groupMenusBySystemType();
                this.renderGroups();
                setTimeout(() => this.initSortable(), 100);
            }
        } catch (error) {
            console.error('Error loading menus:', error);
        }
    },

    openCreateModal() {
        this.modal.mode = 'create';
        this.form = {
            id: null,
            key: '',
            label: '',
            route: '',
            icon: '',
            parent_id: '',
            system_type: 1,
            sort_order: Math.max(...this.allMenus.map(m => m.sort_order || 0), 0) + 1,
            connection_type: 'pgsql',
        };

        document.getElementById('modalTitle').textContent = 'เพิ่มเมนูใหม่';
        document.getElementById('modalSubmitText').textContent = 'สร้างเมนู';
        this.populateForm();
        document.getElementById('menuModal').style.display = 'flex';
    },

    openEditModal(menuId) {
        const menu = this.allMenus.find(m => m.id === menuId);
        if (!menu) return;

        this.modal.mode = 'edit';
        this.form = {
            id: menu.id,
            key: menu.key,
            label: menu.label,
            route: menu.route || '',
            icon: menu.icon || '',
            parent_id: menu.parent_id || '',
            system_type: menu.system_type || 1,
            sort_order: menu.sort_order || 0,
            connection_type: menu.connection_type || 'pgsql',
        };

        document.getElementById('modalTitle').textContent = 'แก้ไขเมนู';
        document.getElementById('modalSubmitText').textContent = 'บันทึกการแก้ไข';
        this.populateForm();
        document.getElementById('menuModal').style.display = 'flex';
    },

    populateForm() {
        document.getElementById('formKey').value = this.form.key;
        document.getElementById('formLabel').value = this.form.label;
        document.getElementById('formRoute').value = this.form.route;
        document.getElementById('formIcon').value = this.form.icon;
        document.getElementById('formParentId').value = this.form.parent_id;
        document.getElementById('formSystemType').value = this.form.system_type;
        document.getElementById('formSortOrder').value = this.form.sort_order;
        document.getElementById('formConnectionType').value = this.form.connection_type;
    },

    closeModal() {
        document.getElementById('menuModal').style.display = 'none';
    },

    async submitForm(event) {
        if (event) event.preventDefault();

        this.form.key = document.getElementById('formKey').value;
        this.form.label = document.getElementById('formLabel').value;
        this.form.route = document.getElementById('formRoute').value;
        this.form.icon = document.getElementById('formIcon').value;
        this.form.parent_id = document.getElementById('formParentId').value || null;
        this.form.system_type = parseInt(document.getElementById('formSystemType').value);
        this.form.sort_order = parseInt(document.getElementById('formSortOrder').value);
        this.form.connection_type = document.getElementById('formConnectionType').value;

        const url = this.modal.mode === 'create' ? '/admin/menus' : `/admin/menus/${this.form.id}`;
        const method = this.modal.mode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form),
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(data.message || 'บันทึกสำเร็จ', 'success');
                this.closeModal();
                await this.loadMenus();
            } else {
                this.showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('เกิดข้อผิดพลาดในการบันทึก', 'error');
        }
    },

    async toggleActive(menuId) {
        try {
            const response = await fetch(`/admin/menus/${menuId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(data.message || 'อัพเดตสถานะสำเร็จ', 'success');
                await this.loadMenus();
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    confirmDelete(menuId) {
        const menu = this.allMenus.find(m => m.id === menuId);
        if (!menu) return;

        this.deleteModal.menu = menu;
        document.getElementById('deleteMenuLabel').textContent = menu.label;
        document.getElementById('deleteModal').style.display = 'flex';
    },

    closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    },

    async deleteMenu() {
        if (!this.deleteModal.menu) return;

        try {
            const response = await fetch(`/admin/menus/${this.deleteModal.menu.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                this.showToast(data.message || 'ลบสำเร็จ', 'success');
                this.closeDeleteModal();
                await this.loadMenus();
            } else {
                this.showToast(data.message || 'เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    openIconPicker() {
        this.renderIcons();
        document.getElementById('iconPickerModal').style.display = 'flex';
    },

    closeIconPicker() {
        document.getElementById('iconPickerModal').style.display = 'none';
        document.getElementById('iconSearch').value = '';
    },

    renderIcons() {
        const search = document.getElementById('iconSearch').value.toLowerCase();
        const filtered = search ? this.icons.filter(icon => icon.name.toLowerCase().includes(search)) : this.icons;

        const grid = document.getElementById('iconGrid');
        grid.innerHTML = `
            <div class="grid grid-cols-6 gap-3">
                ${filtered.map(icon => `
                    <button type="button" onclick="menuManager.selectIcon('${icon.name}')"
                        class="flex flex-col items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3 transition hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                        <svg class="h-8 w-8 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon.path}"/>
                        </svg>
                        <span class="text-xs text-gray-600 dark:text-gray-400">${icon.name}</span>
                    </button>
                `).join('')}
            </div>
        `;
    },

    filterIcons() {
        this.renderIcons();
    },

    selectIcon(name) {
        document.getElementById('formIcon').value = name;
        this.closeIconPicker();
    },

    updateParentMenuSelect() {
        const select = document.getElementById('formParentId');
        const currentValue = select.value;

        select.innerHTML = '<option value="">🔹 เมนูหลัก (Main Menu)</option>';

        this.allMenus.filter(m => !m.parent_id).forEach(menu => {
            const option = document.createElement('option');
            option.value = menu.id;
            option.textContent = `└─ เมนูย่อยของ: ${menu.label}`;
            select.appendChild(option);
        });

        select.value = currentValue;
    },

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const iconSuccess = document.getElementById('toastIconSuccess');
        const iconError = document.getElementById('toastIconError');

        toastMessage.textContent = message;
        toast.className = `fixed top-4 right-4 z-50 max-w-sm rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;

        if (type === 'success') {
            iconSuccess.style.display = 'block';
            iconError.style.display = 'none';
        } else {
            iconSuccess.style.display = 'none';
            iconError.style.display = 'block';
        }

        toast.style.display = 'block';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    },
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    menuManager.init();
});
</script>
@endpush

@endsection
