@props(['menuId', 'companyId' => null])

<!-- Sticky Notes Container -->
<div x-data="stickyNoteManager({{ $menuId }}, {{ $companyId ? $companyId : 'null' }})"
     x-init="init()"
     class="sticky-notes-wrapper">

    <!-- Bottom Right Badges (like Screenshot) -->
    <div class="fixed bottom-6 right-6 z-[100] flex flex-col gap-3">
        <!-- Trash Badge -->
        <button @click="toggleTrash()"
                type="button"
                class="group relative bg-red-500 hover:bg-red-600 text-white rounded-lg px-4 py-3 shadow-lg transition-all duration-200 hover:scale-105 active:scale-95 flex items-center gap-2"
                :class="{'ring-4 ring-red-300': showTrash}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            <span class="font-semibold">ถังขยะ</span>
            <span class="ml-1">( <span x-text="trashedNotes.length"></span> )</span>
        </button>

        <!-- Sticky Note Badge -->
        <button @click="createNote()"
                type="button"
                class="group relative bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded-lg px-4 py-3 shadow-lg transition-all duration-200 hover:scale-105 active:scale-95 flex items-center gap-2 font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>สร้างโน๊ต</span>
            <span class="ml-1">( <span x-text="notes.length"></span> )</span>
        </button>
    </div>

    <!-- Trash Panel (Modal) -->
    <div x-show="showTrash"
         x-cloak
         class="fixed inset-0 z-[99] flex items-center justify-center bg-black/50"
         @click="showTrash = false">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-[48rem] max-h-[42rem] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border-2 border-red-300 dark:border-red-700 overflow-hidden">

        <!-- Trash Header -->
        <div class="p-4 border-b border-red-200 dark:border-red-800 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30">
            <h3 class="text-base font-bold text-red-800 dark:text-red-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                ถังขยะ (<span x-text="trashedNotes.length"></span>)
            </h3>
            <button type="button" @click="showTrash = false"
                    class="p-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Trashed Notes List -->
        <div class="overflow-y-auto max-h-96 p-3">
            <template x-if="trashedNotes.length === 0">
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">ถังขยะว่างเปล่า</p>
                </div>
            </template>

            <template x-for="note in trashedNotes" :key="note.id">
                <div class="mb-2 p-3 rounded-lg border-2 transition-all duration-200 hover:shadow-md"
                     :class="`border-${note.color}-300 bg-${note.color}-50 dark:bg-${note.color}-900/20`">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white line-clamp-2 mb-2"
                               x-text="note.content || 'ว่างเปล่า...'"></p>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span :class="`px-2 py-0.5 rounded-full bg-${note.color}-200 dark:bg-${note.color}-800/50 text-${note.color}-800 dark:text-${note.color}-200 font-medium`"
                                      x-text="note.color"></span>
                                <span x-text="formatDate(note.deleted_at)"></span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <button type="button" @click.stop="restoreFromTrash(note.id)"
                                    class="p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition"
                                    title="กู้คืน">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </button>
                            <button type="button" @click.stop="permanentlyDelete(note.id)"
                                    class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                    title="ลบถาวร">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        </div>
    </div>

    <!-- Sticky Notes (Floating) -->
    <template x-for="note in visibleNotes" :key="note.id">
        <div x-show="!note.is_minimized"
             x-cloak
             x-transition
             :id="'note-' + note.id"
             class="sticky-note fixed rounded-xl shadow-2xl overflow-hidden border-2"
             :class="[getNoteColorClass(note.color), `border-${note.color}-400`]"
             :style="`left: ${note.position_x}px; top: ${note.position_y}px; width: ${note.width}px; height: ${note.height}px; z-index: ${note.z_index};`"
             @mousedown="bringToFront(note)">

            <!-- Note Header -->
            <div class="note-header px-3 py-2 flex items-center justify-between bg-gradient-to-r from-black/10 to-black/5 backdrop-blur-sm"
                 :class="note.is_pinned ? 'cursor-default' : 'cursor-move'"
                 @mousedown="startDrag($event, note)">
                <div class="flex items-center gap-2">
                    <!-- Color Picker -->
                    <div class="relative" x-data="{ showColors: false }">
                        <button type="button" @click.stop="showColors = !showColors"
                                class="w-5 h-5 rounded-full border-2 border-white/80 shadow-sm hover:scale-110 transition"
                                :style="`background-color: ${getColorHex(note.color)}`"
                                title="เปลี่ยนสี"></button>
                        <div x-show="showColors"
                             x-cloak
                             x-transition
                             @click.away="showColors = false"
                             class="absolute top-8 left-0 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-2 flex gap-1.5 z-50 border border-gray-200 dark:border-gray-700">
                            <button type="button" @click.stop="changeNoteColor(note, 'yellow'); showColors = false"
                                    class="w-7 h-7 rounded-full border-2 hover:scale-125 transition-transform"
                                    :class="note.color === 'yellow' ? 'border-gray-800 dark:border-white ring-2 ring-offset-1 ring-gray-800' : 'border-white'"
                                    style="background-color: #fbbf24"
                                    title="เหลือง"></button>
                            <button type="button" @click.stop="changeNoteColor(note, 'blue'); showColors = false"
                                    class="w-7 h-7 rounded-full border-2 hover:scale-125 transition-transform"
                                    :class="note.color === 'blue' ? 'border-gray-800 dark:border-white ring-2 ring-offset-1 ring-gray-800' : 'border-white'"
                                    style="background-color: #60a5fa"
                                    title="น้ำเงิน"></button>
                            <button type="button" @click.stop="changeNoteColor(note, 'green'); showColors = false"
                                    class="w-7 h-7 rounded-full border-2 hover:scale-125 transition-transform"
                                    :class="note.color === 'green' ? 'border-gray-800 dark:border-white ring-2 ring-offset-1 ring-gray-800' : 'border-white'"
                                    style="background-color: #4ade80"
                                    title="เขียว"></button>
                            <button type="button" @click.stop="changeNoteColor(note, 'pink'); showColors = false"
                                    class="w-7 h-7 rounded-full border-2 hover:scale-125 transition-transform"
                                    :class="note.color === 'pink' ? 'border-gray-800 dark:border-white ring-2 ring-offset-1 ring-gray-800' : 'border-white'"
                                    style="background-color: #f472b6"
                                    title="ชมพู"></button>
                            <button type="button" @click.stop="changeNoteColor(note, 'purple'); showColors = false"
                                    class="w-7 h-7 rounded-full border-2 hover:scale-125 transition-transform"
                                    :class="note.color === 'purple' ? 'border-gray-800 dark:border-white ring-2 ring-offset-1 ring-gray-800' : 'border-white'"
                                    style="background-color: #a78bfa"
                                    title="ม่วง"></button>
                            <button type="button" @click.stop="changeNoteColor(note, 'orange'); showColors = false"
                                    class="w-7 h-7 rounded-full border-2 hover:scale-125 transition-transform"
                                    :class="note.color === 'orange' ? 'border-gray-800 dark:border-white ring-2 ring-offset-1 ring-gray-800' : 'border-white'"
                                    style="background-color: #fb923c"
                                    title="ส้ม"></button>
                        </div>
                    </div>

                    <!-- Pin Button -->
                    <button type="button" @click.stop="togglePin(note)"
                            :title="note.is_pinned ? 'Unpin' : 'Pin'"
                            class="p-1 hover:bg-white/30 rounded transition"
                            :class="note.is_pinned ? 'text-yellow-900 dark:text-yellow-200' : 'text-gray-600 dark:text-gray-400'">
                        <svg class="w-4 h-4" :class="note.is_pinned ? 'rotate-45' : ''" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-1">
                    <!-- Minimize Button -->
                    <button type="button" @click.stop="minimizeNote(note)"
                            title="Minimize"
                            class="p-1 hover:bg-white/30 rounded transition text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                        </svg>
                    </button>

                    <!-- Close Button -->
                    <button type="button" @click.stop="deleteNote(note.id)"
                            title="ลบ"
                            class="p-1 hover:bg-red-500/20 rounded transition text-red-600 dark:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Note Content -->
            <div class="note-content p-4 overflow-y-auto" :style="`height: calc(100% - 44px);`">
                <textarea
                    x-model="note.content"
                    @input="debounceUpdate(note)"
                    class="w-full h-full bg-transparent border-none outline-none resize-none text-gray-900 dark:text-gray-100 placeholder-gray-400 text-base leading-relaxed"
                    placeholder="เขียน note ของคุณที่นี่..."
                    style="font-family: 'Segoe UI', 'Tahoma', sans-serif;"></textarea>
            </div>

            <!-- Resize Handle -->
            <div @mousedown.stop="startResize($event, note)"
                 class="absolute bottom-0 right-0 w-8 h-8 cursor-se-resize z-10 flex items-end justify-end p-1 hover:bg-black/10 rounded-bl-lg transition"
                 title="ลากเพื่อขยาย">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17 12h-2v2h2v-2zm0 4h-2v2h2v-2zm-4-4h-2v2h2v-2zm0 4h-2v2h2v-2z"/>
                </svg>
            </div>
        </div>
    </template>
</div>

<style>
[x-cloak] { display: none !important; }

.sticky-note {
    min-width: 220px;
    min-height: 180px;
    max-width: 700px;
    max-height: 700px;
    transition: box-shadow 0.2s ease;
}

.sticky-note:hover {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
}

/* Toast positioning - avoid footer overlap */
.swal-toast-bottom {
    bottom: 80px !important;
}

.bg-yellow-note {
    background: linear-gradient(135deg, #fef9c3 0%, #fde047 50%, #facc15 100%);
}
.bg-blue-note {
    background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 50%, #60a5fa 100%);
}
.bg-green-note {
    background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 50%, #34d399 100%);
}
.bg-pink-note {
    background: linear-gradient(135deg, #fce7f3 0%, #f9a8d4 50%, #f472b6 100%);
}
.bg-purple-note {
    background: linear-gradient(135deg, #f3e8ff 0%, #c084fc 50%, #a855f7 100%);
}
.bg-orange-note {
    background: linear-gradient(135deg, #ffedd5 0%, #fdba74 50%, #fb923c 100%);
}

.note-header {
    user-select: none;
}

.note-content textarea::placeholder {
    font-style: italic;
}

.note-content textarea:focus {
    outline: none;
}

/* Line clamp utility for note preview */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
function stickyNoteManager(menuId, companyId) {
    return {
        menuId: menuId,
        companyId: companyId,
        notes: [],
        trashedNotes: [],
        showTrash: false,
        draggedNote: null,
        resizedNote: null,
        dragOffset: { x: 0, y: 0 },
        updateTimeout: null,
        maxZIndex: 1000,

        async init() {
            await this.loadNotes();
            await this.loadTrashedNotes();

            // Global mouse move handler
            document.addEventListener('mousemove', (e) => {
                if (this.draggedNote) {
                    this.handleDrag(e);
                }
                if (this.resizedNote) {
                    this.handleResize(e);
                }
            });

            // Global mouse up handler
            document.addEventListener('mouseup', () => {
                if (this.draggedNote) {
                    this.stopDrag();
                }
                if (this.resizedNote) {
                    this.stopResize();
                }
            });
        },

        get visibleNotes() {
            return this.notes.filter(n => !n.is_minimized);
        },

        async loadNotes() {
            try {
                const params = new URLSearchParams({
                    menu_id: this.menuId,
                    company_id: this.companyId || ''
                });

                const response = await fetch(`/api/sticky-notes?${params}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    console.error('Load notes failed:', response.status);
                    this.notes = [];
                    // Don't show error on initial load
                    return;
                }

                const data = await response.json();

                if (data.success && data.data) {
                    this.notes = Array.isArray(data.data) ? data.data : [];
                    if (this.notes.length > 0) {
                        this.maxZIndex = Math.max(...this.notes.map(n => n.z_index || 1000)) + 1;
                    }
                } else {
                    this.notes = [];
                }
            } catch (error) {
                console.error('Load notes error:', error);
                this.notes = [];
            }
        },

        async loadTrashedNotes() {
            try {
                const params = new URLSearchParams({
                    menu_id: this.menuId,
                    company_id: this.companyId || ''
                });

                const response = await fetch(`/api/sticky-notes/trash?${params}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    console.error('Load trashed notes failed:', response.status);
                    this.trashedNotes = [];
                    return;
                }

                const data = await response.json();

                if (data.success && data.data) {
                    this.trashedNotes = Array.isArray(data.data) ? data.data : [];
                } else {
                    this.trashedNotes = [];
                }
            } catch (error) {
                console.error('Load trashed notes error:', error);
                this.trashedNotes = [];
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('th-TH', {
                day: 'numeric',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        async createNote() {
            try {
                // Calculate position to avoid sidebar and footer
                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;

                // Avoid sidebar (typically 256px) + some padding
                const sidebarWidth = 280;
                const baseX = Math.floor(sidebarWidth + 20);
                const baseY = Math.floor(120); // Start below header (increased to avoid header overlap)

                // Add offset for multiple notes
                const offsetX = (this.notes.length % 5) * 30;
                const offsetY = Math.floor(this.notes.length / 5) * 30;

                const response = await fetch('/api/sticky-notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        menu_id: this.menuId,
                        company_id: this.companyId,
                        content: '',
                        color: 'yellow',
                        position_x: baseX + offsetX,
                        position_y: baseY + offsetY,
                        width: 300,
                        height: 200,
                        is_minimized: false,
                        is_pinned: false,
                        z_index: ++this.maxZIndex
                    })
                });

                if (!response.ok) {
                    console.error('Create note failed:', response.status, response.statusText);
                    const text = await response.text();
                    console.error('Response:', text.substring(0, 500));
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    this.notes.push(data.data);
                    // Optional: Show success message
                    // Swal.fire({
                    //     icon: 'success',
                    //     title: 'สร้างโน๊ตสำเร็จ',
                    //     timer: 1000,
                    //     showConfirmButton: false
                    // });
                }
            } catch (error) {
                console.error('Create note error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถสร้างโน๊ตได้',
                    confirmButtonColor: '#dc2626'
                });
            }
        },

        async updateNote(note) {
            try {
                // Check if note still exists in our notes array (not deleted)
                if (!this.notes.find(n => n.id === note.id)) {
                    console.log('Note already deleted, skipping update');
                    return;
                }

                const response = await fetch(`/api/sticky-notes/${note.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(note)
                });

                if (!response.ok) {
                    console.error('Update note failed:', response.status);
                }
            } catch (error) {
                console.error('Update note error:', error);
            }
        },

        debounceUpdate(note) {
            clearTimeout(this.updateTimeout);
            this.updateTimeout = setTimeout(() => {
                this.updateNote(note);
            }, 1000);
        },

        async deleteNote(noteId) {
            try {
                // Clear any pending updates for this note
                if (this.updateTimeout) {
                    clearTimeout(this.updateTimeout);
                    this.updateTimeout = null;
                }

                // Remove from notes array immediately to prevent further updates
                this.notes = this.notes.filter(n => n.id !== noteId);

                const response = await fetch(`/api/sticky-notes/${noteId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    console.error('Delete note failed:', response.status);
                    // Reload notes to restore state if delete failed
                    await this.loadNotes();
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    await this.loadTrashedNotes();
                }
            } catch (error) {
                console.error('Delete note error:', error);
                // Reload notes to restore state if error occurred
                await this.loadNotes();
            }
        },

        async restoreFromTrash(noteId) {
            try {
                const response = await fetch(`/api/sticky-notes/${noteId}/restore`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    console.error('Restore note failed:', response.status);
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถกู้คืนได้',
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    await this.loadNotes();
                    await this.loadTrashedNotes();
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: 'กู้คืนสำเร็จ',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            container: 'swal-toast-bottom'
                        }
                    });
                }
            } catch (error) {
                console.error('Restore note error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถกู้คืนได้',
                    confirmButtonColor: '#dc2626'
                });
            }
        },

        async permanentlyDelete(noteId) {
            const result = await Swal.fire({
                title: 'ต้องการลบถาวรหรือไม่?',
                text: 'ไม่สามารถกู้คืนได้อีก!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'ลบถาวร',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`/api/sticky-notes/${noteId}/force`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    console.error('Permanent delete failed:', response.status);
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถลบได้',
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    await this.loadTrashedNotes();
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: 'ลบถาวรสำเร็จ',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            container: 'swal-toast-bottom'
                        }
                    });
                }
            } catch (error) {
                console.error('Permanent delete error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถลบได้',
                    confirmButtonColor: '#dc2626'
                });
            }
        },

        toggleTrash() {
            this.showTrash = !this.showTrash;
        },

        getColorHex(color) {
            const colors = {
                yellow: '#fbbf24',
                blue: '#60a5fa',
                green: '#4ade80',
                pink: '#f472b6',
                purple: '#a78bfa',
                orange: '#fb923c'
            };
            return colors[color] || colors.yellow;
        },

        getNoteColorClass(color) {
            return `bg-${color}-note`;
        },

        changeNoteColor(note, color) {
            note.color = color;
            this.updateNote(note);
        },

        togglePin(note) {
            note.is_pinned = !note.is_pinned;
            this.updateNote(note);
        },

        minimizeNote(note) {
            note.is_minimized = true;
            this.updateNote(note);
        },

        restoreNote(note) {
            note.is_minimized = false;
            this.bringToFront(note);
            this.updateNote(note);
        },

        bringToFront(note) {
            note.z_index = ++this.maxZIndex;
        },

        startDrag(event, note) {
            if (note.is_pinned) return;

            this.draggedNote = note;
            this.dragOffset = {
                x: event.clientX - note.position_x,
                y: event.clientY - note.position_y
            };
            event.preventDefault();
        },

        handleDrag(event) {
            if (!this.draggedNote) return;

            // Calculate new position with boundary checks
            let newX = event.clientX - this.dragOffset.x;
            let newY = event.clientY - this.dragOffset.y;

            // Keep note within window bounds
            newX = Math.max(0, Math.min(window.innerWidth - this.draggedNote.width, newX));
            newY = Math.max(0, Math.min(window.innerHeight - this.draggedNote.height, newY));

            this.draggedNote.position_x = newX;
            this.draggedNote.position_y = newY;
        },

        stopDrag() {
            if (this.draggedNote) {
                this.updateNote(this.draggedNote);
                this.draggedNote = null;
            }
        },

        startResize(event, note) {
            if (note.is_pinned) {
                console.log('Note is pinned, cannot resize');
                return;
            }
            console.log('Start resize', note.id);
            this.resizedNote = note;
            this.bringToFront(note);
            event.preventDefault();
            event.stopPropagation();
        },

        handleResize(event) {
            if (!this.resizedNote) return;

            const newWidth = Math.max(220, Math.min(800, event.clientX - this.resizedNote.position_x));
            const newHeight = Math.max(180, Math.min(800, event.clientY - this.resizedNote.position_y));

            this.resizedNote.width = Math.floor(newWidth);
            this.resizedNote.height = Math.floor(newHeight);
        },

        stopResize() {
            if (this.resizedNote) {
                this.updateNote(this.resizedNote);
                this.resizedNote = null;
            }
        }
    }
}
</script>
