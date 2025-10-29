{{-- Designer Form Component - Sample Data with Controls --}}
<div class="lg:col-span-1">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⚙️ การตั้งค่า</h3>

        <!-- Bank Template -->
        <div class="property-panel">
            <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">📋 เทมเพลตธนาคาร</h4>
            <select id="bank_template" onchange="loadTemplate()" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white">
                <option value="custom">กำหนดเอง</option>
                <option value="scb">ธนาคารไทยพาณิชย์ (SCB)</option>
                <option value="kbank">ธนาคารกสิกรไทย (KBANK)</option>
                <option value="ktb">ธนาคารกรุงไทย (KTB)</option>
            </select>
        </div>

        <!-- Background Image -->
        <div class="property-panel">
            <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">🖼️ รูปพื้นหลังเช็ค</h4>
            <div class="space-y-2">
                <input type="file" id="cheque_bg_upload" accept="image/*"
                    onchange="handleBackgroundUpload(event)"
                    class="w-full text-xs text-gray-900 border border-gray-300 rounded cursor-pointer bg-gray-50 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600" />
                <div class="text-xs text-gray-500 dark:text-gray-400 text-center">หรือ</div>
                <input type="text" id="cheque_bg_url" placeholder="URL รูปภาพ"
                    onchange="loadBackgroundFromUrl(this.value)"
                    class="w-full text-sm rounded border border-gray-300 px-3 py-2 dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
                <button type="button" onclick="removeChequeBackground()"
                    class="w-full rounded bg-red-100 px-3 py-2 text-sm text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-200">
                    🗑️ ลบรูปภาพ
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                💡 รูปจะแสดงเฉพาะใน Preview (ไม่พิมพ์ออกมา)
            </p>
        </div>

        <!-- BOT Standard Size Info -->
        <div class="property-panel">
            <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">📏 ขนาดเช็คมาตรฐาน BOT</h4>
            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
                <div class="flex justify-between">
                    <span>ความยาว:</span>
                    <span class="font-medium">178 mm (7")</span>
                </div>
                <div class="flex justify-between">
                    <span>ความสูง:</span>
                    <span class="font-medium">89 mm (3.5")</span>
                </div>
                <div class="flex justify-between">
                    <span>บนหน้าจอ:</span>
                    <span class="font-medium">890 × 445 px</span>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                ⚖️ ขนาดตามมาตรฐาน BOT (ธนาคารแห่งประเทศไทย)<br>
                รหัส กส650 - ใช้กับทุกธนาคารในประเทศไทย
            </p>
        </div>

        <!-- Element Properties -->
        <div class="property-panel" id="element_properties" style="display:none;">
            <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">✏️ แก้ไข: <span id="selected_element_name">-</span></h4>

            <div class="grid grid-cols-2 gap-2 mb-3">
                <div>
                    <label class="text-sm">ตำแหน่ง X</label>
                    <input type="number" id="prop_x" onchange="updateElementProperty()" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                </div>
                <div>
                    <label class="text-sm">ตำแหน่ง Y</label>
                    <input type="number" id="prop_y" onchange="updateElementProperty()" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
                </div>
            </div>

            <div class="mb-3">
                <label class="text-sm">ขนาดตัวอักษร (px)</label>
                <input type="number" id="prop_font_size" onchange="updateElementProperty()" min="10" max="50" class="w-full rounded border border-gray-300 px-3 py-2 dark:border-gray-700">
            </div>

            <div class="mb-3">
                <label class="text-sm">สี</label>
                <input type="color" id="prop_color" onchange="updateElementProperty()" class="w-full h-10 rounded border border-gray-300 dark:border-gray-700">
            </div>

            <div class="mb-3">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="prop_bold" onchange="updateElementProperty()" class="rounded border-gray-300">
                    <span class="text-sm">ตัวหนา</span>
                </label>
            </div>
        </div>

        <!-- Date Spacing Control -->
        <div class="property-panel">
            <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">📅 ช่องว่างวันที่</h4>
            <label for="date_spacing_designer" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                ช่องว่างระหว่างตัวเลขวันที่
            </label>
            <input id="date_spacing_designer" type="range" min="0" max="8" step="1" value="3" class="w-full"
                   oninput="updateDateSpacingDesigner(this.value)" onchange="saveDateSpacingDesigner(this.value)" />
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                ช่องว่าง: <span id="date_spacing_value_designer">3</span> ตัวอักษร
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2">
            <button onclick="testPrintCheque()" class="w-full rounded bg-blue-500 px-4 py-2.5 text-white hover:bg-blue-600">
                🖨️ ทดสอบพิมพ์
            </button>
            <button onclick="saveAsTemplate()" class="w-full rounded bg-green-500 px-4 py-2.5 text-white hover:bg-green-600">
                💾 บันทึก Template
            </button>
            <button onclick="resetPositions()" class="w-full rounded bg-red-500 px-4 py-2.5 text-white hover:bg-red-600">
                🔄 รีเซ็ตตำแหน่ง
            </button>
        </div>
    </div>
</div>

<style>
.property-panel {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}

.dark .property-panel {
    background: #1f2937;
    border-color: #374151;
}
</style>
