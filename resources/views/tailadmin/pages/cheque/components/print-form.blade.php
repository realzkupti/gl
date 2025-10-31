{{-- Print Form Component - Real Data Input --}}
<div class="w-full">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📝 ข้อมูลเช็ค</h3>

        <form id="cheque-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Branch -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">🏢 สาขา</label>
                <select id="branch_code" name="branch_code" tabindex="1" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                    <option value="">-- เลือกสาขา --</option>
                </select>
            </div>

            <!-- Bank -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">📋 ธนาคาร</label>
                <select id="bank_code" name="bank_code" onchange="loadBankTemplate()" tabindex="2" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                    <option value="custom">กำหนดเอง</option>
                    <option value="scb">ธนาคารไทยพาณิชย์ (SCB)</option>
                    <option value="kbank">ธนาคารกสิกรไทย (KBANK)</option>
                    <option value="ktb">ธนาคารกรุงไทย (KTB)</option>
                </select>
            </div>

            <!-- Cheque Number -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">🔢 หมายเลขเช็ค</label>
                <div class="flex gap-2">
                    <input type="text" id="cheque_number" name="cheque_number" placeholder="เลขที่เช็ค" required
                        onblur="loadChequeByNumber()" tabindex="3"
                        class="flex-1 rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                    <button type="button" onclick="useNextChequeNo()" tabindex="-1" class="rounded bg-gray-100 px-4 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700" title="เลขถัดไป">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">กรอกเลขที่เช็คเดิมเพื่อโหลดข้อมูล</p>
            </div>

            <!-- Date -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">📅 วันที่</label>
                <input type="text" id="date" name="date" required readonly tabindex="4"
                    placeholder="เลือกวันที่"
                    class="w-full rounded border border-gray-300 bg-white px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 cursor-pointer" />
            </div>

            <!-- Payee -->
            <div class="relative">
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">👤 จ่ายให้</label>
                <input type="text" id="payee" name="payee" placeholder="ชื่อผู้รับเงิน" required autocomplete="off" tabindex="5"
                    class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                <div id="payee-autocomplete" class="absolute z-50 mt-1 w-full hidden bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
            </div>

            <!-- Amount -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">💰 จำนวนเงิน (บาท)</label>
                <input type="text" id="amount" name="amount" placeholder="0.00" required oninput="updateAmountText()" tabindex="6"
                    class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                <p id="amount-text" class="mt-1 text-sm text-gray-600 dark:text-gray-400"></p>
            </div>

            <!-- Checkboxes -->
            <div>
                <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                    <input type="checkbox" id="show_ac_payee" checked onchange="toggleAcPayee()" tabindex="-1"
                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                    แสดง A/C PAYEE ONLY
                </label>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                    <input type="checkbox" id="show_line" checked onchange="toggleLine()" tabindex="-1"
                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                    แสดงเส้น (หรือผู้ถือ)
                </label>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                    <input type="checkbox" id="auto_clear_after_print" onchange="saveAutoClearSetting()" tabindex="-1"
                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                    เริ่มใหม่อัตโนมัติหลังพิมพ์
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-2 md:col-span-2 lg:col-span-4">
                <button type="button" onclick="printCheque()" tabindex="7" class="flex-1 rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                    🖨️ พิมพ์เช็ค
                </button>
                <button type="button" onclick="clearForm()" tabindex="8" class="flex-1 rounded border border-gray-300 px-6 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    🔄 เริ่มใหม่
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Controls for Print Tab -->
<div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <label for="date_spacing" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Spaces between date characters</label>
        <input id="date_spacing" type="range" min="0" max="8" step="1" value="3" class="w-full"
               oninput="updateDateSpacingLabel(this.value)" onchange="saveDateSpacing(this.value); updateDateDisplay();" />
        <div class="text-[11px] text-gray-500 dark:text-gray-400">Spaces per character: <span id="date_spacing_value">3</span></div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Font size (selected element)</span>
            <span id="font_size_value" class="text-[11px] text-gray-500 dark:text-gray-400">-</span>
        </div>
        <input id="font_size_slider" type="range" min="10" max="40" step="1" value="18" class="w-full" disabled
               oninput="onFontSizeSlide(this.value)" onchange="onFontSizeCommit(this.value)" />
        <div class="mt-2">
            <label class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                <input id="bold_toggle" type="checkbox" class="rounded border-gray-300"
                       onchange="onBoldToggle(this.checked)" disabled>
                <span>Bold</span>
            </label>
        </div>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Print offset (px)</div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label for="print_offset_x" class="block text-[11px] text-gray-500 dark:text-gray-400">Offset X</label>
                <input id="print_offset_x" type="number" step="1" value="0"
                       class="w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-700"
                       oninput="savePrintOffsets()" />
            </div>
            <div>
                <label for="print_offset_y" class="block text-[11px] text-gray-500 dark:text-gray-400">Offset Y</label>
                <input id="print_offset_y" type="number" step="1" value="0"
                       class="w-full rounded border border-gray-300 px-2 py-1 text-sm dark:border-gray-700"
                       oninput="savePrintOffsets()" />
            </div>
        </div>
        <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">ใช้สำหรับชดเชยความคลาดเคลื่อนของเครื่องพิมพ์</p>
    </div>
</div>
