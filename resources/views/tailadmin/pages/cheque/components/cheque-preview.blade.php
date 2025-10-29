{{-- Cheque Preview Component - Shared between Print and Designer tabs --}}
<div class="cheque-workspace">
    <div class="mb-4">
        <span class="info-badge">💡 คลิกที่องค์ประกอบเพื่อแก้ไข</span>
        <span class="info-badge">🖱️ ลากเพื่อย้ายตำแหน่ง</span>
        <span class="info-badge">⌨️ ใช้ลูกศรเพื่อปรับตำแหน่งละเอียด</span>
    </div>

    <!-- Wrapper for print -->
    <div class="print-cheque-container">
        <div class="cheque-preview" id="chequePreview">
            <div class="draggable ac-payee" id="acPayee" data-name="A/C PAYEE ONLY">
                A/C PAYEE ONLY
            </div>
            <div class="draggable line-holder" id="lineHolder" data-name="เส้น (หรือผู้ถือ)">
                --------
            </div>
            <div class="draggable" id="dateDisplay" data-name="วันที่"></div>
            <div class="draggable" id="payeeDisplay" data-name="ชื่อผู้รับเงิน">
                &lt;สั่งจ่าย&gt;
            </div>
            <div class="draggable" id="amountText" data-name="จำนวนเงิน (ตัวอักษร)"></div>
            <div class="draggable" id="amountNumber" data-name="จำนวนเงิน (ตัวเลข)">
                ***0.00***
            </div>
        </div>
    </div>
</div>
