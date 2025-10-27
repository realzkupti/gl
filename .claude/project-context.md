# 🤖 AI Assistant Context - Laravel Multi-Company System

> **เอกสารนี้สำหรับ AI อ่านเพื่อเข้าใจบริบทโปรเจค**
>
> อัพเดทล่าสุด: 27 ตุลาคม 2025

---

## 📋 ภาพรวมโปรเจค

### ชื่อโปรเจค
**GL System** - ระบบจัดการสิทธิ์และ Multi-Company Accounting

### Stack เทคโนโลยี
- **Backend**: Laravel 11 (PHP)
- **Frontend**: Blade Templates + Vanilla JavaScript (ไม่ใช้ Alpine.js)
- **Database**: PostgreSQL (ระบบหลัก) + MySQL (Bplus ต่อบริษัท)
- **CSS**: Tailwind CSS
- **Assets**: Vite

### วัตถุประสงค์หลัก
ระบบมี **2 ส่วนหลัก**:

1. **ระบบหลัก (System)** - PostgreSQL
   - จัดการผู้ใช้และสิทธิ์
   - ระบบเช็ค (Cheque)
   - ระบบอื่นๆ ที่จะมาในอนาคต (OCR, แปลงเอกสาร)

2. **ระบบ Bplus** - MySQL (แยกฐานข้อมูลต่อบริษัท)
   - ระบบบัญชีแบบ Express Accounting
   - งบทดลอง, ผังบัญชี, สมุดรายวัน, งบการเงิน
   - **ต้องเลือกบริษัทก่อนใช้งาน**

---

## 🗂️ โครงสร้างฐานข้อมูล

### ตารางสำคัญ (PostgreSQL)

#### sys_users
```sql
- id
- name
- email
- password
- department_id → FK to sys_departments
- is_approved (boolean) - รออนุมัติหรือไม่
- created_at, updated_at
- created_by → FK to sys_users ⭐
- updated_by → FK to sys_users ⭐
```

#### sys_menus
```sql
- id
- key (unique) - เช่น 'admin_users', 'bplus_dashboard'
- label - ชื่อแสดง
- route - Laravel route name
- icon - ชื่อไอคอน
- parent_id → FK to sys_menus (nullable) - เมนูลูก
- system_type - 1=System, 2=Bplus ⭐ (เดิมชื่อ department_id)
- sort_order - ลำดับการแสดง
- connection_type - 'pgsql' หรือ 'mysql'
- is_active (boolean)
- is_system (boolean) - ลบไม่ได้
- created_at, updated_at
- created_by, updated_by ⭐
```

#### sys_departments
```sql
- id
- key - 'system', 'bplus', 'admin', 'user'
- label - ชื่อแสดง
- sort_order
- created_at, updated_at
- created_by, updated_by ⭐

# แผนกพื้นฐาน 4 แผนก:
1. System (id=1) - กลุ่มเมนูระบบ
2. Bplus (id=2) - กลุ่มเมนู Bplus
3. Admin (id=3) - แผนก Admin (user ที่อยู่ในนี้เห็นทุกเมนู)
4. User (id=4) - แผนก User ทั่วไป (default เมื่อสมัครใหม่)
```

#### sys_department_menu_permissions
```sql
- id
- department_id → FK to sys_departments
- menu_id → FK to sys_menus
- created_at, updated_at
- created_by, updated_by ⭐

# ความหมาย: แผนก X มีสิทธิ์เข้าถึงเมนู Y
```

#### sys_user_menu_permissions
```sql
- id
- user_id → FK to sys_users
- menu_id → FK to sys_menus
- created_at, updated_at
- created_by, updated_by ⭐

# ความหมาย: User X มีสิทธิ์พิเศษเข้าถึงเมนู Y (override แผนก)
```

#### sys_user_company_access
```sql
- id
- user_id → FK to sys_users
- company_id → FK to companies
- created_at, updated_at
- created_by, updated_by ⭐

# ความหมาย: User X เข้าถึงบริษัท Y ได้ (สำหรับ Bplus)
```

#### companies
```sql
- id
- name
- tax_id
- logo (URL/path)
- db_host - MySQL host
- db_name - Database name
- db_user
- db_password
- created_at, updated_at
- created_by, updated_by ⭐

# บริษัทแต่ละแห่ง = ฐานข้อมูล MySQL แยกกัน
```

---

## 🎯 ระบบสิทธิ์ (Permissions)

### ลำดับการตรวจสอบสิทธิ์

1. **Admin Override**: ถ้าเป็น `admin@local` → มีสิทธิ์ทุกอย่าง
2. **User-specific Permissions**: ถ้ามีสิทธิ์ใน `sys_user_menu_permissions` → ใช้สิทธิ์นี้
3. **Department Permissions**: ใช้สิทธิ์จาก `sys_department_menu_permissions` ตาม department_id
4. **Deny**: ถ้าไม่มีสิทธิ์เลย → ห้ามเข้าถึง

### การกำหนดสิทธิ์

- **Admin Department (id=3)**: เห็นทุกเมนู
- **User Department (id=4)**: เห็นแค่บางเมนู (เช่น Bplus Dashboard)
- **แผนกอื่นๆ**: Admin สร้างและกำหนดสิทธิ์เอง

---

## 🔧 Code Standards & Rules

### ⭐ กฎสำคัญ #1: created_by และ updated_by

**ทุกตาราง** ต้องมี:
```sql
created_by BIGINT UNSIGNED NULL
updated_by BIGINT UNSIGNED NULL
FOREIGN KEY (created_by) REFERENCES sys_users(id) ON DELETE SET NULL
FOREIGN KEY (updated_by) REFERENCES sys_users(id) ON DELETE SET NULL
```

**ทุก Model** ต้องใช้ Trait:
```php
use App\Traits\HasUserTracking;

class YourModel extends Model
{
    use HasUserTracking;
}
```

**Trait จะ auto-fill**:
- `created_by` = current user เมื่อ create
- `updated_by` = current user เมื่อ update

### กฎสำคัญ #2: ไม่ใช้ Alpine.js

- ใช้ **Vanilla JavaScript** ทั้งหมด
- ไม่ใช้ `x-data`, `x-if`, `x-for`, `x-show` ฯลฯ
- ใช้ `addEventListener`, `querySelector`, `innerHTML` แทน

### กฎสำคัญ #3: Naming Convention

**Tables**: `sys_` prefix (เช่น `sys_users`, `sys_menus`)
**Models**: ไม่มี prefix (เช่น `User`, `Menu`)
**Controllers**: ไว้ใน `app/Http/Controllers/Admin/` (เช่น `MenuController`, `UserController`)
**Views**: ไว้ใน `resources/views/admin/` (เช่น `menus-simple.blade.php`, `users.blade.php`)

### กฎสำคัญ #4: Routes

```php
Route::middleware(['auth', 'menu.permission'])->group(function () {
    Route::get('admin/menus', [MenuController::class, 'index'])->name('admin.menus');
    // ...
});
```

### กฎสำคัญ #5: Fetch API

ใช้ **Fetch API** สำหรับ AJAX:
```javascript
const response = await fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
    },
    body: JSON.stringify(data)
});
```

---

## 📂 โครงสร้างไฟล์

```
d:\Herd\gl\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── MenuController.php
│   │   │       ├── UserController.php
│   │   │       ├── DepartmentController.php
│   │   │       └── DepartmentPermissionController.php
│   │   └── Middleware/
│   │       ├── MenuPermission.php
│   │       └── ActivityLogger.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Menu.php
│   │   ├── Department.php
│   │   ├── Company.php
│   │   ├── DepartmentMenuPermission.php
│   │   └── UserMenuPermission.php
│   ├── Traits/
│   │   └── HasUserTracking.php ⭐
│   └── Support/
│       └── Perm.php (Helper สำหรับเช็คสิทธิ์)
├── database/
│   ├── migrations/
│   │   ├── 2025_10_25_150000_rename_tables_with_sys_prefix.php
│   │   ├── 2025_10_25_151000_add_department_support.php
│   │   ├── 2025_10_25_152000_create_user_company_access.php
│   │   └── 2025_10_25_153000_seed_bplus_department_and_menus.php
│   └── seeders/
│       ├── DepartmentSeeder.php
│       ├── SystemMenusSeeder.php
│       └── DepartmentPermissionsSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── menus-simple.blade.php ⭐ (หน้าจัดการเมนู - Vanilla JS)
│       │   ├── users.blade.php (หน้าจัดการผู้ใช้ - 2 tabs)
│       │   ├── departments.blade.php
│       │   └── department-permissions.blade.php
│       ├── components/
│       │   └── company-switch-modal.blade.php ⭐ (Modal เปลี่ยนบริษัท)
│       └── tailadmin/
│           ├── layouts/
│           │   └── app.blade.php
│           └── partials/
│               ├── sidebar.blade.php ⭐
│               └── menu-item.blade.php
├── routes/
│   └── web.php
├── MIGRATION_GUIDE.md (คู่มือ Migration เก่า - Role → Department)
├── SYSTEM_RESTRUCTURE_PLAN.md ⭐ (แม่บทปรับปรุงระบบครั้งที่ 2)
└── .claude/
    └── project-context.md (ไฟล์นี้)
```

---

## 🎨 หน้าหลักๆ ของระบบ

### 1. จัดการเมนู (`/admin/menus`)

**ฟีเจอร์**:
- 2 Tabs: [ระบบ] [Bplus]
- Tree structure (parent-child)
- Drag & drop เปลี่ยนลำดับ
- Drag & drop สลับ parent-child (ถามยืนยันก่อน)
- เพิ่ม/แก้ไข/ลบเมนู (Modal)
- Toggle เปิด/ปิดใช้งาน

**Tech**:
- Vanilla JavaScript
- SortableJS (CDN)
- Fetch API

**ไฟล์**: `resources/views/admin/menus-simple.blade.php`

### 2. จัดการผู้ใช้ (`/admin/users`)

**ฟีเจอร์**:
- 2 Tabs: [ผู้ใช้ทั้งหมด] [รออนุมัติ]
- แก้ไขผู้ใช้: ชื่อ, Email, แผนก, บริษัทที่เข้าถึง
- อนุมัติ/ปฏิเสธผู้ใช้ใหม่

**User ใหม่**:
- สมัครเอง → `department_id = 4` (User)
- `is_approved = false` (รออนุมัติ)

**ไฟล์**: `resources/views/admin/users.blade.php`

### 3. Sidebar

**ฟีเจอร์**:
- แสดงโลโก์บริษัทปัจจุบัน (ถ้าเลือก Bplus)
- เมนู "เปลี่ยนบริษัท" (เปิด Modal)
- แสดงเฉพาะเมนูที่มีสิทธิ์
- แยกเป็นกลุ่ม: ระบบ, Bplus

**ไฟล์**: `resources/views/tailadmin/partials/sidebar.blade.php`

### 4. Modal เปลี่ยนบริษัท

**ฟีเจอร์**:
- แสดงรายการบริษัทที่เข้าถึงได้
- เลือกบริษัท → บันทึกใน Session
- Reload หน้า → โลโก์และข้อมูลเปลี่ยน
- Logout → ต้องเลือกใหม่

**ไฟล์**: `resources/views/components/company-switch-modal.blade.php`

---

## 🚀 Workflow สำคัญ

### การ Login

1. User login
2. ตรวจสอบ `is_approved`
3. ถ้า approved → เข้าสู่ระบบ
4. ถ้า Bplus user → ต้องเลือกบริษัทก่อน
5. เก็บบริษัทใน `session('current_company')`

### การตรวจสอบสิทธิ์เมนู

```php
// ใน MenuPermission Middleware
$user = auth()->user();
$route = $request->route()->getName();

if (!$user->hasMenuAccess($route)) {
    return redirect()->route('home')->with('forbidden', 'ไม่มีสิทธิ์');
}
```

### การแสดงเมนูใน Sidebar

```php
$accessibleMenus = auth()->user()->getAccessibleMenus();
// → รวมสิทธิ์จาก department + user-specific
```

### การสร้าง/แก้ไข Record

```php
// Model ใช้ HasUserTracking trait
// created_by และ updated_by จะถูก auto-fill

Menu::create([
    'key' => 'test_menu',
    'label' => 'Test',
    // ...
    // created_by = auth()->id() (auto)
    // updated_by = auth()->id() (auto)
]);
```

---

## 📌 สถานะปัจจุบัน (27 ตุลาคม 2025)

### ✅ สำเร็จแล้ว

- [x] ย้ายจาก Role-based → Department-based permissions
- [x] สร้างระบบเมนู Tree Structure แบบ Vanilla JS
- [x] Drag & Drop เปลี่ยนลำดับ
- [x] Drag & Drop สลับ parent-child

### 🚧 กำลังดำเนินการ

- [ ] เปลี่ยน `department_id` → `system_type` ใน sys_menus
- [ ] เพิ่ม `created_by`/`updated_by` ทุกตาราง
- [ ] เพิ่มฟีเจอร์เปลี่ยนบริษัท
- [ ] รวมหน้า users + user-approvals

### 📋 แผนถัดไป

ดู: `SYSTEM_RESTRUCTURE_PLAN.md` (8 Phases)

---

## 🎯 เมื่อ AI เริ่มทำงาน

### ขั้นตอน

1. **อ่านเอกสารนี้ทั้งหมด**
2. **ถาม User**: "คุณต้องการให้ช่วยอะไรครับ?"
3. **ถ้าเกี่ยวกับ System Restructure**: อ่าน `SYSTEM_RESTRUCTURE_PLAN.md`
4. **ถ้าต้องการ debug/fix**: ใช้ context จากเอกสารนี้

### สิ่งที่ต้องจำ

- ✅ **ไม่ใช้ Alpine.js** - ใช้ Vanilla JS
- ✅ **ทุกตารางต้องมี** created_by/updated_by
- ✅ **ทุก Model ต้องใช้** HasUserTracking trait
- ✅ **เมนูแยก 2 ระบบ**: System (1) และ Bplus (2)
- ✅ **สิทธิ์ลำดับ**: Admin override > User-specific > Department > Deny

### คำสั่งที่ใช้บ่อย

```bash
# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed --class=DepartmentSeeder
php artisan db:seed --class=SystemMenusSeeder
php artisan db:seed --class=DepartmentPermissionsSeeder

# Clear caches
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Build assets
npm run build
```

---

## 📞 ติดต่อ & Support

- **User**: ผู้ใช้ที่คุยกับ AI อยู่
- **Repository**: d:\Herd\gl
- **Environment**: Windows + Laravel Herd

---

**🤖 เอกสารนี้สร้างสำหรับ AI Assistant**
**อัพเดทล่าสุด**: 27 ตุลาคม 2025
