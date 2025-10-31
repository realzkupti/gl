@extends('tailadmin.layouts.app')

@section('title', 'จัดการเมนู - ' . config('app.name'))

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
.tree-indent {
    width: 24px;
    display: inline-block;
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
.hidden {
    display: none;
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
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">ปรับแต่งเมนูและจัดเรียงลำดับของแต่ละแผนก</p>
        </div>
        <button onclick="menuManager.openCreateModal()" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            เพิ่มเมนู
        </button>
    </div>

    <!-- Department Tabs -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-4" id="department-tabs">
            @foreach($departments as $dept)
            <button
                onclick="menuManager.switchDepartment({{ $dept->id }})"
                data-dept="{{ $dept->id }}"
                class="dept-tab whitespace-nowrap border-b-2 py-3 px-4 text-sm font-medium transition border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                {{ $dept->label }}
            </button>
            @endforeach
        </nav>
    </div>

    <!-- Menu Table -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">เมนู</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Route</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">ลำดับ</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">สถานะ</th>
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
            <p class="text-gray-600 dark:text-gray-400">ไม่มีเมนูในแผนกนี้</p>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div class="rounded-lg shadow-lg p-4 max-w-sm">
            <div class="flex items-center gap-3">
                <svg id="toast-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
                <p id="toast-message" class="text-sm font-medium"></p>
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

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Route</label>
                            <input type="text" id="form-route" name="route" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Icon</label>
                            <select id="form-icon" name="icon" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach(config('icons') as $key => $icon)
                                <option value="{{ $key }}">{{ $icon['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">แผนก</label>
                            <select id="form-department" name="department_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->label }}</option>
                                @endforeach
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Menu Manager - Vanilla JavaScript
const menuManager = {
    allMenus: @json($menus),
    departments: @json($departments),
    selectedDepartment: {{ isset($departments[0]) ? $departments[0]->id : 1 }},
    expanded: [],
    sortableInstance: null,
    deleteMenuId: null,

    init() {
        console.log('Menu Manager initialized', {
            menus: this.allMenus.length,
            departments: this.departments.length
        });

        // Set first department as active
        this.switchDepartment(this.selectedDepartment);
        this.updateDepartmentTabs();
    },

    getCurrentMenus() {
        return this.allMenus.filter(m => m.department_id == this.selectedDepartment);
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

    switchDepartment(deptId) {
        this.selectedDepartment = deptId;
        this.updateDepartmentTabs();
        this.renderMenus();
        this.initSortable();
    },

    updateDepartmentTabs() {
        document.querySelectorAll('.dept-tab').forEach(tab => {
            const deptId = parseInt(tab.dataset.dept);
            if (deptId === this.selectedDepartment) {
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
                    ${!isChild && this.hasChildren(menu.id) ? `
                    <button onclick="menuManager.toggleExpand(${menu.id})" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                        <svg class="w-4 h-4 transition-transform ${this.isExpanded(menu.id) ? 'rotate-90' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    ` : ''}
                    <div class="w-${isChild ? '6' : '8'} h-${isChild ? '6' : '8'} rounded ${isChild ? 'bg-gray-200 dark:bg-gray-700' : 'bg-brand-50 dark:bg-brand-900/20'} flex items-center justify-center">
                        <svg class="w-${isChild ? '4' : '5'} h-${isChild ? '4' : '5'} ${isChild ? 'text-gray-600 dark:text-gray-300' : 'text-brand-600 dark:text-brand-400'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-${isChild ? 'sm' : 'base'} font-medium text-gray-900 dark:text-white">${menu.label}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">${menu.key}</div>
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

    getIconPath(icon) {
        // Icons loaded from centralized config
        const icons = @json(config('icons'));
        return icons[icon]?.path || 'M4 6h16M4 12h16M4 18h16';
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
        const draggedId = parseInt(evt.item.dataset.id);
        const draggedMenu = this.allMenus.find(m => m.id === draggedId);
        if (!draggedMenu) return;

        // Get all rows and find position
        const allRows = Array.from(document.getElementById('menu-tbody').querySelectorAll('tr'));
        const draggedIndex = allRows.indexOf(evt.item);

        // Check if dropped on/after a parent menu
        let newParentId = null;
        let previousRow = allRows[draggedIndex - 1];

        // If previous row is a parent menu, this becomes its child
        if (previousRow && previousRow.dataset.parent === 'true') {
            const previousId = parseInt(previousRow.dataset.id);
            const previousMenu = this.allMenus.find(m => m.id === previousId);

            // Ask user if they want to make it a child
            if (previousMenu && !draggedMenu.parent_id) {
                const makeChild = confirm(`ต้องการให้ "${draggedMenu.label}" เป็นเมนูลูกของ "${previousMenu.label}" หรือไม่?\n\nกด OK = เป็นเมนูลูก\nกด Cancel = เรียงลำดับเท่านั้น`);
                if (makeChild) {
                    newParentId = previousId;
                }
            }
        }

        // If dragged from child to parent (not after any parent)
        if (draggedMenu.parent_id && !newParentId) {
            const makeParent = confirm(`ต้องการให้ "${draggedMenu.label}" เป็นเมนูหลักหรือไม่?\n\nกด OK = เป็นเมนูหลัก\nกด Cancel = ยกเลิก`);
            if (!makeParent) {
                this.renderMenus(); // Reset
                return;
            }
            newParentId = null; // Explicitly set to null to become parent
        }

        // Update parent if changed
        if (newParentId !== draggedMenu.parent_id) {
            await this.updateMenuParent(draggedId, newParentId);
        }

        // Update sort order for all menus
        await this.updateSortOrder();
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
                    department_id: menu.department_id,
                    parent_id: newParentId,
                    sort_order: menu.sort_order,
                    connection_type: menu.connection_type,
                    is_active: menu.is_active
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

    openCreateModal() {
        document.getElementById('modal-title').textContent = 'เพิ่มเมนูใหม่';
        document.getElementById('form-mode').value = 'create';
        document.getElementById('menu-form').reset();
        document.getElementById('form-department').value = this.selectedDepartment;
        document.getElementById('form-active').checked = true;
        this.updateParentOptions(this.selectedDepartment);
        document.getElementById('modal-overlay').classList.remove('hidden');
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
        document.getElementById('form-department').value = menu.department_id;
        document.getElementById('form-sort-order').value = menu.sort_order;
        document.getElementById('form-connection-type').value = menu.connection_type || 'pgsql';
        document.getElementById('form-active').checked = menu.is_active;

        this.updateParentOptions(menu.department_id, menu.id);
        document.getElementById('form-parent').value = menu.parent_id || '';

        document.getElementById('modal-overlay').classList.remove('hidden');
    },

    updateParentOptions(departmentId, excludeId = null) {
        const select = document.getElementById('form-parent');
        const parents = this.allMenus.filter(m =>
            m.department_id == departmentId &&
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
            department_id: parseInt(formData.get('department_id')),
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
                    this.allMenus.push(result.data);
                } else {
                    const index = this.allMenus.findIndex(m => m.id === result.data.id);
                    if (index > -1) {
                        this.allMenus[index] = result.data;
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

// Update parent options when department changes
document.getElementById('form-department').addEventListener('change', (e) => {
    menuManager.updateParentOptions(parseInt(e.target.value));
});
</script>
@endpush

@endsection
