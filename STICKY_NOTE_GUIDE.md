# 📝 Sticky Note System - คู่มือการใช้งาน

## ✨ ฟีเจอร์หลัก

### 🎯 ระบบ Sticky Note แบบใหม่
- ✅ **บันทึกถาวรใน Database** - ไม่ใช่ localStorage อีกต่อไป
- ✅ **แยกตาม User + Menu + Company** - แต่ละคนมี note ของตัวเอง
- ✅ **6 สีสันสดใส** - Yellow, Blue, Green, Pink, Purple, Gray
- ✅ **Drag & Drop** - ลากวาง note ไปไหนก็ได้
- ✅ **Resize ได้** - ปรับขนาดตามต้องการ
- ✅ **Pin Note** - ปักหมุดแล้วไม่เลื่อนได้
- ✅ **Minimize/Restore** - ซ่อนแล้วเปิดจาก panel
- ✅ **Auto-save** - บันทึกอัตโนมัติทุก 1 วินาที

---

## 📋 ส่วนประกอบของระบบ

### 1. Database Tables

**`sys_menus`** - เพิ่ม column ใหม่:
- `has_sticky_note` (boolean) - เปิด/ปิด sticky note สำหรับเมนูนี้

**`sys_companies`** - เพิ่ม column ใหม่:
- `logo` (varchar) - path ของโลโก้บริษัท

**`sys_sticky_notes`** (ตารางใหม่) - เก็บข้อมูล sticky notes:
```sql
- id (bigint)
- user_id (bigint) - เจ้าของ note
- menu_id (bigint) - เมนูที่ใช้
- company_id (bigint, nullable) - บริษัท (null = ใช้กับทุกบริษัท)
- content (text) - เนื้อหา note
- color (varchar) - สี (yellow/blue/green/pink/purple/gray)
- position_x, position_y (integer) - ตำแหน่ง
- width, height (integer) - ขนาด
- is_minimized (boolean) - ซ่อนอยู่หรือไม่
- is_pinned (boolean) - ปักหมุดหรือไม่
- z_index (integer) - ลำดับการแสดง
- created_at, updated_at
```

### 2. API Endpoints

```
GET    /api/sticky-notes              - ดึง notes ทั้งหมดของ user+menu+company
POST   /api/sticky-notes              - สร้าง note ใหม่
PUT    /api/sticky-notes/{id}         - อัปเดต note
DELETE /api/sticky-notes/{id}         - ลบ note
POST   /api/sticky-notes/bulk-update  - อัปเดตหลาย notes พร้อมกัน
```

### 3. Models & Controllers

**Models:**
- `App\Models\StickyNote` - จัดการข้อมูล sticky notes
- `App\Models\Menu` - เพิ่ม field `has_sticky_note`
- `App\Models\Company` - เพิ่ม field `logo`

**Controllers:**
- `App\Http\Controllers\StickyNoteController` - API สำหรับ CRUD sticky notes
- `App\Http\Controllers\Admin\CompanyController` - จัดการบริษัท + logo upload
- `App\Http\Controllers\Admin\MenuController` - จัดการเมนู + sticky note toggle

### 4. Views & Components

**Component:**
- `resources/views/components/sticky-note.blade.php` - Alpine.js component

**Updated Views:**
- `resources/views/trial_balance_plain.blade.php`
- `resources/views/tailadmin/pages/trial-balance.blade.php`
- `resources/views/admin/menus-system.blade.php` - เพิ่ม checkbox
- `resources/views/admin/companies.blade.php` - เพิ่ม logo upload

---

## 🎨 วิธีใช้งาน

### สำหรับ Admin - เปิด/ปิด Sticky Note

1. ไปที่เมนู **จัดการเมนู** (`/admin/menus`)
2. เลือก Tab ระบบที่ต้องการ (ระบบ หรือ Bplus)
3. คลิกแก้ไขเมนูที่ต้องการ
4. ✅ เลือก **"เปิดใช้งาน Sticky Note"**
5. บันทึก

### สำหรับ User - ใช้งาน Sticky Note

**การเปิด Panel:**
- คลิกปุ่มลอย (Floating Button) สีเหลืองมุมล่างขวา

**สร้าง Note ใหม่:**
1. คลิกปุ่ม + ใน Panel
2. พิมพ์ข้อความ
3. บันทึกอัตโนมัติ

**เปลี่ยนสี:**
- คลิกวงกลมสีที่มุมบนซ้ายของ note
- เลือกสีที่ต้องการ

**Pin Note (ปักหมุด):**
- คลิกไอคอนหมุด
- Note จะไม่สามารถลากได้ (ป้องกันเลื่อนไปโดยไม่ตั้งใจ)

**Minimize (ซ่อน):**
- คลิกปุ่ม - (minimize)
- Note จะหายไป แต่ยังอยู่ใน Panel
- คลิกที่ note ใน Panel เพื่อแสดงอีกครั้ง

**ลบ Note:**
- คลิกปุ่ม X
- หรือคลิกลบใน Panel

---

## 🔧 วิธีเพิ่ม Sticky Note ในหน้าใหม่

### ขั้นตอนที่ 1: เปิด Sticky Note สำหรับเมนู

```php
// ใน database หรือ admin panel
UPDATE sys_menus
SET has_sticky_note = true
WHERE route = 'your.route.name';
```

### ขั้นตอนที่ 2: ส่ง Menu Data ไปยัง View

**ใน Controller:**
```php
public function index()
{
    $currentMenu = \App\Models\Menu::where('route', 'your.route.name')->first();

    return view('your.view', [
        // ... other data
        'currentMenu' => $currentMenu,
    ]);
}
```

### ขั้นตอนที่ 3: เพิ่ม Component ใน View

**ก่อน `@endsection`:**
```blade
{{-- Sticky Note Component --}}
@if(isset($currentMenu) && $currentMenu && $currentMenu->has_sticky_note)
    <x-sticky-note
        :menu-id="$currentMenu->id"
        :company-id="session('current_company_id')"
    />
@endif

@endsection
```

---

## 🎯 เมนูที่เปิดใช้งาน Sticky Note แล้ว

- ✅ งบทดลอง (`trial-balance`)
- ✅ งบทดลอง (ธรรมดา) (`trial-balance.plain`)
- ✅ งบทดลอง (แยกสาขา) (`trial-balance.branch`)

---

## 📊 Admin - จัดการบริษัท (ปรับปรุงแล้ว)

### ฟีเจอร์ใหม่:
- ✅ **อัปโหลดโลโก้** - รองรับ JPG, PNG, GIF (ไม่เกิน 2MB)
- ✅ **แสดงโลโก้ในตาราง** - พร้อม placeholder ถ้าไม่มี
- ✅ **ลบโลโก้ได้** - ปุ่มลบแยกต่างหาก
- ✅ **ทดสอบเชื่อมต่อแก้ไขแล้ว** - ใช้ JavaScript submit แทน GET

---

## 🐛 Troubleshooting

### Sticky Note ไม่แสดง?

**ตรวจสอบ:**
1. เมนูเปิด `has_sticky_note` หรือยัง?
   ```sql
   SELECT id, label, route, has_sticky_note
   FROM sys_menus
   WHERE route = 'your.route';
   ```

2. Controller ส่ง `currentMenu` หรือยัง?
   ```php
   dd($currentMenu); // ใน view
   ```

3. Component ถูกเรียกหรือยัง?
   ```blade
   @if(isset($currentMenu))
       <p>Menu ID: {{ $currentMenu->id }}</p>
   @endif
   ```

### Error: Class 'StickyNote' not found?

**วิธีแก้:**
```bash
composer dump-autoload
php artisan optimize:clear
```

### Note บันทึกไม่ได้?

**ตรวจสอบ:**
1. CSRF Token มีหรือไม่?
   ```blade
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

2. Routes มีครบหรือไม่?
   ```bash
   php artisan route:list | grep sticky
   ```

---

## 📝 License & Credits

- **Alpine.js** - Reactive framework
- **Tailwind CSS** - Styling
- **Laravel** - Backend framework

Developed with ❤️ by Claude Code
