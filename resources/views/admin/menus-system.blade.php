@extends('tailadmin.layouts.app')

@section('title', 'จัดการเมนู')

@push('styles')
<style>
.tree-row {
    transition: background-color 0.15s;
}
.tree-row:hover {
    background-color: rgba(249, 250, 251, 1);
}
.dark .tree-row:hover {
    background-color: rgba(31, 41, 55, 1);
}
.sortable-ghost {
    opacity: 0.4;
    background: #e5e7eb;
}
.drag-handle {
    cursor: grab;
    opacity: 0.5;
}
.drag-handle:hover {
    opacity: 1;
}
.drag-handle:active {
    cursor: grabbing;
}
.child-row {
    display: none;
}
.child-row.show {
    display: table-row;
}
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">จัดการเมนูระบบ</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">ปรับแต่งเมนูและจัดเรียงลำดับของแต่ละระบบ</p>
        </div>
        <button onclick="menuManager.openCreateModal()" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            เพิ่มเมนู
        </button>
    </div>

    <!-- System Tabs -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-4" id="system-tabs">
            <button
                onclick="menuManager.switchSystem(1)"
                data-system="1"
                class="system-tab whitespace-nowrap border-b-2 py-3 px-4 text-sm font-medium transition {{ $systemType === 1 ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                ระบบ (System)
            </button>
            <button
                onclick="menuManager.switchSystem(2)"
                data-system="2"
                class="system-tab whitespace-nowrap border-b-2 py-3 px-4 text-sm font-medium transition {{ $systemType === 2 ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Bplus
            </button>
        </nav>
    </div>

    <!-- Menus Table -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">เมนู</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Route</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">ลำดับ</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">สถานะ</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Sticky Note</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">จัดการ</th>
                </tr>
            </thead>
            <tbody id="menu-tbody" class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- JavaScript will populate this -->
            </tbody>
        </table>

        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">ไม่มีเมนูในระบบนี้</p>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 z-[9999] hidden">
        <div class="rounded-lg shadow-lg p-4 max-w-sm bg-green-500 text-white">
            <div class="flex items-center gap-3">
                <svg id="toast-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                <p id="toast-message" class="text-sm font-medium"></p>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="if(event.target === this) menuManager.closeModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6" onclick="event.stopPropagation()">
            <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">เพิ่มเมนูใหม่</h3>

            <form id="menu-form" onsubmit="menuManager.submitForm(event)">
                <input type="hidden" id="form-id" name="id">
                <input type="hidden" id="form-mode" value="create">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Key</label>
                        <input type="text" id="form-key" name="key" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Label</label>
                        <input type="text" id="form-label" name="label" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Route</label>
                    <input type="text" id="form-route" name="route" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Icon</label>
                    <input type="hidden" id="form-icon" name="icon" value="home">
                    <button type="button" onclick="menuManager.openIconSelector()" class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center" id="selected-icon-preview">
                                <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path id="selected-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                            <span id="selected-icon-name" class="text-gray-900 dark:text-white font-medium">Home</span>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ระบบ</label>
                        <select id="form-system-type" name="system_type" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="1">ระบบ (System)</option>
                            <option value="2">Bplus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">เมนูหลัก</label>
                        <select id="form-parent" name="parent_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">-- ไม่มี --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ลำดับ</label>
                        <input type="number" id="form-sort-order" name="sort_order" value="0" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ประเภทการเชื่อมต่อ</label>
                    <select id="form-connection-type" name="connection_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="pgsql">PostgreSQL</option>
                        <option value="sqlsrv">SQL Server</option>
                        <option value="mysql">MySQL</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 mb-6">
                    <input type="checkbox" id="form-active" name="is_active" value="1" checked class="rounded border-gray-300 dark:border-gray-600">
                    <label for="form-active" class="text-sm font-medium text-gray-700 dark:text-gray-300">เปิดใช้งาน</label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="menuManager.closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition">
                        บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="if(event.target === this) menuManager.closeDeleteModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ยืนยันการลบ</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">คุณต้องการลบเมนู "<span id="delete-menu-name"></span>" ใช่หรือไม่?</p>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="menuManager.closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    ยกเลิก
                </button>
                <button type="button" onclick="menuManager.confirmDelete()" class="px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition">
                    ลบ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Make Child Modal -->
<div id="confirm-child-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="if(event.target === this) menuManager.closeConfirmChildModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ยืนยันการเปลี่ยนแปลง</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                ต้องการให้ "<span id="confirm-child-menu-name" class="font-semibold"></span>" เป็นเมนูลูกของ "<span id="confirm-child-parent-name" class="font-semibold"></span>" หรือไม่?
            </p>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="menuManager.confirmMakeChild(false)" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    เรียงลำดับเท่านั้น
                </button>
                <button type="button" onclick="menuManager.confirmMakeChild(true)" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition">
                    เป็นเมนูลูก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Make Parent Modal -->
<div id="confirm-parent-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="if(event.target === this) menuManager.closeConfirmParentModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">ยืนยันการเปลี่ยนแปลง</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                ต้องการให้ "<span id="confirm-parent-menu-name" class="font-semibold"></span>" เป็นเมนูหลักหรือไม่?
            </p>
            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="menuManager.confirmMakeParent(false)" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    ยกเลิก
                </button>
                <button type="button" onclick="menuManager.confirmMakeParent(true)" class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition">
                    เป็นเมนูหลัก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Icon Selector Modal -->
<div id="icon-selector-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[60] hidden" onclick="if(event.target === this) menuManager.closeIconSelector()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">เลือก Icon</h3>
                <button onclick="menuManager.closeIconSelector()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3" id="icon-grid">
                <!-- Icons will be rendered here by JavaScript -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Menu Manager - Adapted for system_type instead of department_id
const menuManager = {
    allMenus: @json($menus),
    selectedSystem: {{ $systemType }},
    expanded: [],
    sortableInstance: null,
    deleteMenuId: null,
    pendingDragAction: null,
    draggedEvent: null,

    init() {
        console.log('Menu Manager initialized', {
            menus: this.allMenus.length,
            selectedSystem: this.selectedSystem
        });

        this.updateSystemTabs();
        this.renderMenus();
        this.initSortable();
    },

    getCurrentMenus() {
        // Since we fetch menus filtered by system_type from API,
        // allMenus already contains only the menus for selectedSystem
        // So we don't need to filter again
        return this.allMenus;
    },

    hasChildren(parentId) {
        return this.allMenus.some(m => m.parent_id === parentId);
    },

    getChildren(parentId) {
        return this.allMenus.filter(m => m.parent_id === parentId).sort((a, b) => a.sort_order - b.sort_order);
    },

    isExpanded(id) {
        return this.expanded.includes(id);
    },

    toggleExpand(id) {
        const index = this.expanded.indexOf(id);
        if (index > -1) {
            this.expanded.splice(index, 1);
        } else {
            this.expanded.push(id);
        }
        this.renderMenus();
    },

    async switchSystem(systemType) {
        // Don't reload if already on this system
        if (this.selectedSystem === systemType) return;

        // Update selected system
        this.selectedSystem = systemType;

        // Update tab styles
        this.updateSystemTabs();

        // Fetch menus for the new system_type
        await this.loadMenusBySystemType(systemType);

        // Re-render menus with new data
        this.renderMenus();

        // Update URL without reload (optional - for browser history)
        if (history.pushState) {
            const newUrl = `/admin/menus/system?system_type=${systemType}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
        }
    },

    async loadMenusBySystemType(systemType) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/admin/menus/list?system_type=${systemType}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });

            if (!response.ok) {
                console.error('Failed to load menus:', response.status);
                this.showToast('ไม่สามารถโหลดเมนูได้', 'error');
                return;
            }

            const data = await response.json();
            if (data.success && data.data) {
                // Update allMenus with the new data
                this.allMenus = data.data;
            }
        } catch (error) {
            console.error('Load menus error:', error);
            this.showToast('เกิดข้อผิดพลาดในการโหลดเมนู', 'error');
        }
    },

    updateSystemTabs() {
        document.querySelectorAll('.system-tab').forEach(tab => {
            const systemType = parseInt(tab.dataset.system);
            if (systemType === this.selectedSystem) {
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                tab.classList.add('border-brand-500', 'text-brand-600', 'dark:text-brand-400');
            } else {
                tab.classList.remove('border-brand-500', 'text-brand-600', 'dark:text-brand-400');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            }
        });
    },

    renderMenus() {
        const tbody = document.getElementById('menu-tbody');
        const emptyState = document.getElementById('empty-state');
        const currentMenus = this.getCurrentMenus();
        const parentMenus = currentMenus.filter(m => !m.parent_id).sort((a, b) => a.sort_order - b.sort_order);

        tbody.innerHTML = '';

        if (parentMenus.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        parentMenus.forEach(menu => {
            // Parent row
            const tr = this.createMenuRow(menu, false);
            tbody.appendChild(tr);

            // Child rows
            if (this.hasChildren(menu.id)) {
                const children = this.getChildren(menu.id);
                children.forEach(child => {
                    const childTr = this.createMenuRow(child, true, menu.id);
                    tbody.appendChild(childTr);
                });
            }
        });
    },

    createMenuRow(menu, isChild, parentId = null) {
        const tr = document.createElement('tr');
        tr.dataset.id = menu.id;
        tr.dataset.parent = isChild ? 'false' : 'true';
        tr.className = isChild ? 'child-row bg-gray-50/50 dark:bg-gray-800/50' : 'tree-row';

        if (isChild && parentId) {
            if (this.isExpanded(parentId)) {
                tr.classList.add('show');
            }
        }

        const iconPath = this.getIconPath(menu.icon);

        tr.innerHTML = `
            <td class="px-4 py-3 text-center">
                <svg class="drag-handle w-5 h-5 inline-block text-gray-400 cursor-grab hover:text-gray-600 dark:hover:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </td>
            <td class="px-4 py-${isChild ? '2' : '3'}">
                <div class="flex items-center gap-2 ${isChild ? 'ml-8' : ''}">
                    <div class="w-${isChild ? '6' : '8'} h-${isChild ? '6' : '8'} rounded ${isChild ? 'bg-gray-200 dark:bg-gray-700' : 'bg-brand-50 dark:bg-brand-900/20'} flex items-center justify-center">
                        <svg class="w-${isChild ? '4' : '5'} h-${isChild ? '4' : '5'} ${isChild ? 'text-gray-600 dark:text-gray-300' : 'text-brand-600 dark:text-brand-400'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2 flex-1">
                        <div>
                            <div class="text-${isChild ? 'sm' : 'base'} font-medium text-gray-900 dark:text-white">${menu.label}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">${menu.key}</div>
                        </div>
                        ${!isChild && this.hasChildren(menu.id) ? `
                        <button onclick="menuManager.toggleExpand(${menu.id})" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded ml-2">
                            <svg class="w-4 h-4 transition-transform ${this.isExpanded(menu.id) ? 'rotate-90' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        ` : ''}
                    </div>
                </div>
            </td>
            <td class="px-4 py-${isChild ? '2' : '3'} text-sm text-gray-600 dark:text-gray-400">${menu.route || '-'}</td>
            <td class="px-4 py-${isChild ? '2' : '3'} text-center text-sm text-gray-500 dark:text-gray-400">${menu.sort_order}</td>
            <td class="px-4 py-${isChild ? '2' : '3'} text-center">
                <button onclick="menuManager.toggleActive(${menu.id})" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${menu.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'}">
                    ${menu.is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน'}
                </button>
            </td>
            <td class="px-4 py-${isChild ? '2' : '3'} text-center">
                <button onclick="menuManager.toggleStickyNote(${menu.id})" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${menu.has_sticky_note ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'}">
                    ${menu.has_sticky_note ? 'เปิดใช้งาน' : 'ปิดใช้งาน'}
                </button>
            </td>
            <td class="px-4 py-${isChild ? '2' : '3'} text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="menuManager.openEditModal(${menu.id})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded dark:text-blue-400 dark:hover:bg-blue-900/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    ${!menu.is_system ? `
                    <button onclick="menuManager.openDeleteModal(${menu.id}, '${menu.label}')" class="p-1.5 text-red-600 hover:bg-red-50 rounded dark:text-red-400 dark:hover:bg-red-900/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    ` : ''}
                </div>
            </td>
        `;

        return tr;
    },

    getIconData() {
        // Icons loaded from centralized config
        return @json(config('icons'));
    },

    getIconPath(icon) {
        const iconData = this.getIconData();
        return iconData[icon]?.path || 'M4 6h16M4 12h16M4 18h16';
    },

    getIconName(icon) {
        const iconData = this.getIconData();
        return iconData[icon]?.name || icon;
    },

    initSortable() {
        if (typeof Sortable === 'undefined') {
            console.warn('Sortable.js not loaded');
            return;
        }

        if (this.sortableInstance) {
            this.sortableInstance.destroy();
        }

        const tbody = document.getElementById('menu-tbody');
        if (!tbody) return;

        this.sortableInstance = new Sortable(tbody, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: (evt) => {
                this.handleDragEnd(evt);
            }
        });
    },

    async handleDragEnd(evt) {
        this.draggedEvent = evt;
        const draggedId = parseInt(evt.item.dataset.id);
        const draggedMenu = this.allMenus.find(m => m.id === draggedId);
        if (!draggedMenu) return;

        // Get all rows and find position
        const allRows = Array.from(document.getElementById('menu-tbody').querySelectorAll('tr'));
        const draggedIndex = allRows.indexOf(evt.item);

        // Find what parent context we're in based on position
        let contextParentId = null;
        for (let i = draggedIndex - 1; i >= 0; i--) {
            const row = allRows[i];
            if (row.dataset.parent === 'true') {
                contextParentId = parseInt(row.dataset.id);
                break;
            }
        }

        const previousRow = allRows[draggedIndex - 1];
        const isAfterParent = previousRow && previousRow.dataset.parent === 'true';

        // Case 1: Parent menu being moved to be child of another parent
        if (!draggedMenu.parent_id && isAfterParent) {
            const previousId = parseInt(previousRow.dataset.id);
            const previousMenu = this.allMenus.find(m => m.id === previousId);

            this.pendingDragAction = {
                menuId: draggedId,
                previousMenuId: previousId,
                type: 'child'
            };
            this.openConfirmChildModal(draggedMenu.label, previousMenu.label);
            return;
        }

        // Case 2: Child menu being moved to parent level (not under any parent context)
        if (draggedMenu.parent_id && contextParentId === null) {
            const oldParent = this.allMenus.find(m => m.id === draggedMenu.parent_id);
            const oldParentName = oldParent ? oldParent.label : 'เมนูแม่';

            if (!confirm(`ต้องการย้าย "${draggedMenu.label}" ออกจาก "${oldParentName}" เป็นเมนูหลักใช่หรือไม่?`)) {
                this.renderMenus(); // Reset order
                return;
            }

            this.pendingDragAction = {
                menuId: draggedId,
                type: 'parent'
            };
            await this.processDragAction(false, true);
            return;
        }

        // Case 3: Child menu being moved to different parent
        if (draggedMenu.parent_id && contextParentId && draggedMenu.parent_id !== contextParentId) {
            const oldParent = this.allMenus.find(m => m.id === draggedMenu.parent_id);
            const newParent = this.allMenus.find(m => m.id === contextParentId);
            const oldParentName = oldParent ? oldParent.label : 'เมนูแม่';
            const newParentName = newParent ? newParent.label : 'เมนูแม่';

            if (!confirm(`ต้องการย้าย "${draggedMenu.label}" จาก "${oldParentName}" ไปยัง "${newParentName}" ใช่หรือไม่?`)) {
                this.renderMenus(); // Reset order
                return;
            }

            await this.updateMenuParent(draggedId, contextParentId);
            await this.updateSortOrder();
            return;
        }

        // Case 4: Child menu being reordered within same parent
        if (draggedMenu.parent_id && contextParentId === draggedMenu.parent_id) {
            const parentMenu = this.allMenus.find(m => m.id === draggedMenu.parent_id);
            const parentName = parentMenu ? parentMenu.label : 'เมนูแม่';

            if (!confirm(`ต้องการเปลี่ยนลำดับ "${draggedMenu.label}" ภายใต้ "${parentName}" ใช่หรือไม่?`)) {
                this.renderMenus(); // Reset order
                return;
            }
        }

        // Case 5: Parent menu reordering (no confirmation needed for parent level)
        // Just update sort order
        await this.updateSortOrder();
    },

    async processDragAction(makeChild, makeParent) {
        if (!this.pendingDragAction) return;

        const action = this.pendingDragAction;
        this.pendingDragAction = null;

        if (action.type === 'child') {
            if (makeChild) {
                await this.updateMenuParent(action.menuId, action.previousMenuId);
            }
            await this.updateSortOrder();
        } else if (action.type === 'parent') {
            if (makeParent) {
                await this.updateMenuParent(action.menuId, null);
            } else {
                this.renderMenus(); // Reset
                return;
            }
            await this.updateSortOrder();
        }
    },

    async updateMenuParent(menuId, newParentId) {
        const menu = this.allMenus.find(m => m.id === menuId);
        if (!menu) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/admin/menus/api/${menuId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    key: menu.key,
                    label: menu.label,
                    route: menu.route,
                    icon: menu.icon,
                    system_type: menu.system_type,
                    parent_id: newParentId,
                    sort_order: menu.sort_order,
                    connection_type: menu.connection_type || 'pgsql',
                    is_active: menu.is_active,
                    has_sticky_note: menu.has_sticky_note || false
                })
            });

            const data = await response.json();
            if (data.success) {
                menu.parent_id = newParentId;
                this.showToast('อัพเดทเมนูหลักสำเร็จ', 'success');
                this.renderMenus();
            }
        } catch (error) {
            console.error('Update parent error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    async updateSortOrder() {
        const tbody = document.getElementById('menu-tbody');
        const allRows = Array.from(tbody.querySelectorAll('tr'));

        // Update sort order for all visible menus
        const updates = [];
        let parentOrder = 0;
        let childOrder = {};

        allRows.forEach(row => {
            const menuId = parseInt(row.dataset.id);
            const menu = this.allMenus.find(m => m.id === menuId);
            if (!menu) return;

            if (!menu.parent_id) {
                // Parent menu
                menu.sort_order = parentOrder;
                updates.push({ id: menuId, sort_order: parentOrder });
                parentOrder++;
                childOrder[menuId] = 0;
            } else {
                // Child menu
                const parentId = menu.parent_id;
                if (childOrder[parentId] === undefined) {
                    childOrder[parentId] = 0;
                }
                menu.sort_order = childOrder[parentId];
                updates.push({ id: menuId, sort_order: childOrder[parentId] });
                childOrder[parentId]++;
            }
        });

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch('/admin/menus/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order: updates })
            });

            const data = await response.json();
            if (data.success) {
                this.showToast('อัพเดทลำดับสำเร็จ', 'success');
                this.renderMenus();
            }
        } catch (error) {
            console.error('Reorder error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    async toggleActive(menuId) {
        const menu = this.allMenus.find(m => m.id === menuId);
        if (!menu) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/admin/menus/api/${menuId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                menu.is_active = !menu.is_active;
                this.renderMenus();
                this.showToast('อัพเดทสถานะสำเร็จ', 'success');
            }
        } catch (error) {
            console.error('Toggle error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    async toggleStickyNote(menuId) {
        const menu = this.allMenus.find(m => m.id === menuId);
        if (!menu) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // Ensure system_type is set correctly (use selectedSystem as fallback)
            const systemType = menu.system_type || this.selectedSystem;

            const response = await fetch(`/admin/menus/api/${menuId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    key: menu.key,
                    label: menu.label,
                    route: menu.route,
                    icon: menu.icon,
                    system_type: systemType,
                    parent_id: menu.parent_id,
                    sort_order: menu.sort_order,
                    connection_type: menu.connection_type || 'pgsql',
                    is_active: menu.is_active,
                    has_sticky_note: !menu.has_sticky_note
                })
            });

            const data = await response.json();
            if (data.success) {
                // Update the menu object with the response from server to preserve all fields
                if (data.menu) {
                    Object.assign(menu, data.menu);
                } else {
                    menu.has_sticky_note = !menu.has_sticky_note;
                }
                this.renderMenus();
                this.showToast(menu.has_sticky_note ? 'เปิดใช้งาน Sticky Note แล้ว' : 'ปิดใช้งาน Sticky Note แล้ว', 'success');
            }
        } catch (error) {
            console.error('Toggle sticky note error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    openCreateModal() {
        document.getElementById('modal-title').textContent = 'เพิ่มเมนูใหม่';
        document.getElementById('form-mode').value = 'create';
        document.getElementById('menu-form').reset();
        document.getElementById('form-system-type').value = this.selectedSystem;
        document.getElementById('form-active').checked = true;
        this.updateParentOptions(this.selectedSystem);

        // Calculate default sort_order (last position in parent menus)
        this.updateSortOrderField();

        // Add event listener to parent select to update sort_order
        const parentSelect = document.getElementById('form-parent');
        parentSelect.addEventListener('change', () => this.updateSortOrderField());

        document.getElementById('modal-overlay').classList.remove('hidden');
    },

    updateSortOrderField() {
        const parentId = document.getElementById('form-parent').value;
        let maxSortOrder = 0;

        if (parentId) {
            // Has parent - get max sort_order of children
            const children = this.allMenus.filter(m => m.parent_id === parseInt(parentId));
            if (children.length > 0) {
                maxSortOrder = Math.max(...children.map(m => m.sort_order || 0));
            }
        } else {
            // No parent - get max sort_order of root menus
            const rootMenus = this.allMenus.filter(m => !m.parent_id && m.system_type === this.selectedSystem);
            if (rootMenus.length > 0) {
                maxSortOrder = Math.max(...rootMenus.map(m => m.sort_order || 0));
            }
        }

        document.getElementById('form-sort-order').value = maxSortOrder + 1;
    },

    openEditModal(menuId) {
        const menu = this.allMenus.find(m => m.id === menuId);
        if (!menu) return;

        document.getElementById('modal-title').textContent = 'แก้ไขเมนู';
        document.getElementById('form-mode').value = 'edit';
        document.getElementById('form-id').value = menu.id;
        document.getElementById('form-key').value = menu.key;
        document.getElementById('form-label').value = menu.label;
        document.getElementById('form-route').value = menu.route || '';
        document.getElementById('form-icon').value = menu.icon;
        document.getElementById('form-system-type').value = menu.system_type;
        document.getElementById('form-sort-order').value = menu.sort_order;
        document.getElementById('form-connection-type').value = menu.connection_type || 'pgsql';
        document.getElementById('form-active').checked = menu.is_active;

        // Update icon preview
        const iconData = this.getIconData();
        const icon = iconData[menu.icon] || iconData['home'];
        document.getElementById('selected-icon-name').textContent = icon.name;
        document.getElementById('selected-icon-path').setAttribute('d', icon.path);

        this.updateParentOptions(menu.system_type, menu.id);
        document.getElementById('form-parent').value = menu.parent_id || '';

        document.getElementById('modal-overlay').classList.remove('hidden');
    },

    updateParentOptions(systemType, excludeId = null) {
        const select = document.getElementById('form-parent');
        const parents = this.allMenus.filter(m =>
            m.system_type == systemType &&
            !m.parent_id &&
            (!excludeId || m.id !== excludeId)
        );

        select.innerHTML = '<option value="">-- ไม่มี --</option>';
        parents.forEach(menu => {
            const option = document.createElement('option');
            option.value = menu.id;
            option.textContent = menu.label;
            select.appendChild(option);
        });
    },

    closeModal() {
        document.getElementById('modal-overlay').classList.add('hidden');
    },

    async submitForm(event) {
        event.preventDefault();

        const mode = document.getElementById('form-mode').value;
        const formData = new FormData(event.target);

        const data = {
            key: formData.get('key'),
            label: formData.get('label'),
            route: formData.get('route') || null,
            icon: formData.get('icon'),
            system_type: parseInt(formData.get('system_type')),
            parent_id: formData.get('parent_id') ? parseInt(formData.get('parent_id')) : null,
            sort_order: parseInt(formData.get('sort_order')),
            connection_type: formData.get('connection_type'),
            is_active: formData.get('is_active') === '1'
        };

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            let url, method;

            if (mode === 'create') {
                url = '/admin/menus/api';
                method = 'POST';
            } else {
                const id = document.getElementById('form-id').value;
                url = `/admin/menus/api/${id}`;
                method = 'PUT';
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                if (mode === 'create') {
                    this.allMenus.push(result.menu);
                } else {
                    const index = this.allMenus.findIndex(m => m.id === result.menu.id);
                    if (index > -1) {
                        this.allMenus[index] = result.menu;
                    }
                }

                this.renderMenus();
                this.closeModal();
                this.showToast(mode === 'create' ? 'เพิ่มเมนูสำเร็จ' : 'แก้ไขเมนูสำเร็จ', 'success');
            } else {
                this.showToast('เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Submit error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

    openDeleteModal(menuId, menuLabel) {
        this.deleteMenuId = menuId;
        document.getElementById('delete-menu-name').textContent = menuLabel;
        document.getElementById('delete-modal').classList.remove('hidden');
    },

    closeDeleteModal() {
        this.deleteMenuId = null;
        document.getElementById('delete-modal').classList.add('hidden');
    },

    openConfirmChildModal(menuLabel, parentLabel) {
        document.getElementById('confirm-child-menu-name').textContent = menuLabel;
        document.getElementById('confirm-child-parent-name').textContent = parentLabel;
        document.getElementById('confirm-child-modal').classList.remove('hidden');
    },

    closeConfirmChildModal() {
        document.getElementById('confirm-child-modal').classList.add('hidden');
        if (!this.pendingDragAction) {
            this.renderMenus(); // Reset if cancelled
        }
    },

    async confirmMakeChild(makeChild) {
        this.closeConfirmChildModal();
        await this.processDragAction(makeChild, false);
    },

    openConfirmParentModal(menuLabel) {
        document.getElementById('confirm-parent-menu-name').textContent = menuLabel;
        document.getElementById('confirm-parent-modal').classList.remove('hidden');
    },

    closeConfirmParentModal() {
        document.getElementById('confirm-parent-modal').classList.add('hidden');
        if (!this.pendingDragAction) {
            this.renderMenus(); // Reset if cancelled
        }
    },

    async confirmMakeParent(makeParent) {
        this.closeConfirmParentModal();
        await this.processDragAction(false, makeParent);
    },

    openIconSelector() {
        const iconGrid = document.getElementById('icon-grid');
        const iconData = this.getIconData();

        iconGrid.innerHTML = '';

        Object.entries(iconData).forEach(([key, data]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex flex-col items-center gap-2 p-4 rounded-lg border-2 border-transparent hover:border-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition cursor-pointer';
            button.onclick = () => this.selectIcon(key);

            const currentIcon = document.getElementById('form-icon').value;
            if (key === currentIcon) {
                button.className += ' border-brand-500 bg-brand-50 dark:bg-brand-900/20';
            }

            button.innerHTML = `
                <div class="w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${data.path}"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-600 dark:text-gray-400 text-center">${data.name}</span>
            `;

            iconGrid.appendChild(button);
        });

        document.getElementById('icon-selector-modal').classList.remove('hidden');
    },

    closeIconSelector() {
        document.getElementById('icon-selector-modal').classList.add('hidden');
    },

    selectIcon(iconKey) {
        const iconData = this.getIconData();
        const icon = iconData[iconKey];

        // Update hidden input
        document.getElementById('form-icon').value = iconKey;

        // Update preview
        document.getElementById('selected-icon-name').textContent = icon.name;
        document.getElementById('selected-icon-path').setAttribute('d', icon.path);

        this.closeIconSelector();
    },

    async confirmDelete() {
        if (!this.deleteMenuId) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const response = await fetch(`/admin/menus/api/${this.deleteMenuId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const index = this.allMenus.findIndex(m => m.id === this.deleteMenuId);
                if (index > -1) {
                    this.allMenus.splice(index, 1);
                }
                this.renderMenus();
                this.closeDeleteModal();
                this.showToast('ลบเมนูสำเร็จ', 'success');
            } else {
                this.showToast('เกิดข้อผิดพลาด', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showToast('เกิดข้อผิดพลาด', 'error');
        }
    },

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
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    menuManager.init();
});

// Update parent options when system type changes
document.getElementById('form-system-type').addEventListener('change', (e) => {
    menuManager.updateParentOptions(parseInt(e.target.value));
});
</script>
@endpush

@if(isset($currentMenu) && $currentMenu && $currentMenu->has_sticky_note)
    <x-sticky-note
        :menu-id="$currentMenu->id"
        :company-id="session('current_company_id')"
    />
@endif

@endsection
