# 📘 แม่บทการปรับปรุงระบบเมนูและสิทธิ์ (System Restructure Master Plan)

> **เอกสารนี้เป็นแม่บทสำหรับการปรับปรุงระบบเมนูและสิทธิ์ครั้งที่ 2**
>
> ใช้เป็นคู่มืออ้างอิงเมื่อต้องการดำเนินการต่อในครั้งถัดไป (กรณี token หมดหรือ session ใหม่)

**วันที่สร้าง**: 27 ตุลาคม 2025
**สถานะ**: 🟡 อยู่ระหว่างดำเนินการ
**Progress**: Phase 0 - Planning Complete
**ไฟล์นี้สร้างโดย**: AI Assistant (Claude)

---

## 🎯 วัตถุประสงค์

ปรับโครงสร้างระบบเมนูและสิทธิ์ให้แยกเป็น **2 ระบบหลัก**:

1. **ระบบ (System)** - เมนูหลักของระบบ (สิทธิ์, เช็ค, OCR, ฯลฯ)
2. **Bplus** - ระบบบัญชี Bplus (ต้องเลือกบริษัทก่อนใช้งาน)

### ฟีเจอร์หลักที่เพิ่มเติม:

- ✅ จัดการเมนูแบบ Tree Structure (Parent-Child)
- ✅ Drag & Drop เปลี่ยนลำดับและสลับ Parent-Child
- ✅ เปลี่ยนบริษัท (สำหรับ Bplus)
- ✅ จัดการสิทธิ์ตามแผนก
- ✅ **บันทึก created_by/updated_by ทุกตาราง** ⭐

---

## 📊 ภาพรวมการเปลี่ยนแปลง

### เดิม (Before)

```
sys_menus
├── department_id → แยกเมนูตามแผนก (ผิดแนวคิด)
├── parent_id
└── sort_order

จัดการเมนู → มี Tab หลายแผนก (บัญชี, การเงิน, IT, ฯลฯ)
Sidebar → แสดงเมนูทั้งหมดไม่เลือก
```

### ใหม่ (After)

```
sys_menus
├── system_type → แยกเป็น 2 ระบบ (1=System, 2=Bplus)
├── parent_id → รองรับ Tree Structure
└── sort_order → เรียงลำดับ

sys_users
├── department_id → user อยู่แผนกไหน
├── created_by → ผู้สร้าง ⭐
└── updated_by → ผู้แก้ไขล่าสุด ⭐

จัดการเมนู → มี 2 Tab: [ระบบ] [Bplus]
Sidebar → แสดงเฉพาะเมนูที่มีสิทธิ์ + โลโก์บริษัทปัจจุบัน
```

---

## 📦 สรุป 8 Phases

| Phase | หัวข้อ | สถานะ | เวลา |
|-------|--------|-------|------|
| **Phase 1** | ปรับโครงสร้างฐานข้อมูล | ⬜ Pending | 30 นาที |
| **Phase 2** | ปรับ Model และ Logic | ⬜ Pending | 20 นาที |
| **Phase 3** | ปรับหน้าจัดการเมนู | ⬜ Pending | 20 นาที |
| **Phase 4** | เพิ่มฟีเจอร์เปลี่ยนบริษัท | ⬜ Pending | 40 นาที |
| **Phase 5** | ปรับหน้าจัดการผู้ใช้ | ⬜ Pending | 30 นาที |
| **Phase 6** | ตั้งค่าสิทธิ์แผนกเริ่มต้น | ⬜ Pending | 20 นาที |
| **Phase 7** | อัพเดท Middleware & Sidebar | ⬜ Pending | 20 นาที |
| **Phase 8** | Testing & Cleanup | ⬜ Pending | 20 นาที |

**รวม**: ประมาณ 2.5-3 ชั่วโมง

---

## 🔑 คำตอบจาก User (สำคัญ!)

### Q1: ชื่อ field ในตาราง sys_menus
**ตอบ**: ตัวเลือก A - เปลี่ยนชื่อ `department_id` → `system_type`

### Q2: การเก็บบริษัทที่เลือกปัจจุบัน
**ตอบ**: ตัวเลือก A - Session (Login ใหม่ต้องเลือกใหม่)

### Q3: เมนู "เปลี่ยนบริษัท" อยู่ตรงไหน?
**ตอบ**: ตัวเลือก A - เป็นเมนูใน Sidebar + แสดงโลโก้บริษัทที่ Sidebar Header

### Q4: หน้าจัดการ User ปัจจุบัน
**ตอบ**: มีแล้ว (`admin/users` + `admin/user-approvals`) → รวม 2 หน้าเป็น 1 หน้า 2 tabs

### Q5: สิทธิ์แผนกเริ่มต้น
**ตอบ**:
- แผนก **Admin** → เห็นเมนูทั้งหมด
- แผนก **User** → เห็นแค่เมนูเดียว (default)
- แผนกอื่นๆ → Admin จัดการเอง

### Q6: Migration ข้อมูลเดิม
**ตอบ**: เปลี่ยนชื่อตาม Q1 และอ้างอิงตาม `connection_type`:
- `connection_type = 'pgsql'` → `system_type = 1` (System)
- `connection_type = 'mysql'` → `system_type = 2` (Bplus)

---

## 📦 Phase 1: ปรับโครงสร้างฐานข้อมูล

### 🎯 เป้าหมาย

- เปลี่ยน `department_id` → `system_type` ในตาราง `sys_menus`
- เพิ่ม `department_id` ในตาราง `sys_users`
- สร้างแผนกเริ่มต้น (System, Bplus, Admin, User)
- **เพิ่ม `created_by` และ `updated_by` ทุกตาราง** ⭐

### 📝 Migrations ที่ต้องสร้าง

#### 1. Rename department_id → system_type

```php
// database/migrations/2025_10_27_100000_rename_department_id_to_system_type_in_menus.php

Schema::table('sys_menus', function (Blueprint $table) {
    $table->renameColumn('department_id', 'system_type');
});

// Migrate data based on connection_type
DB::table('sys_menus')->where('connection_type', 'pgsql')->update(['system_type' => 1]);
DB::table('sys_menus')->where('connection_type', 'mysql')->update(['system_type' => 2]);

// Update sys_departments
DB::table('sys_departments')->truncate();
DB::table('sys_departments')->insert([
    ['id' => 1, 'key' => 'system', 'label' => 'ระบบ', 'sort_order' => 1],
    ['id' => 2, 'key' => 'bplus', 'label' => 'Bplus', 'sort_order' => 2],
    ['id' => 3, 'key' => 'admin', 'label' => 'Admin', 'sort_order' => 3],
    ['id' => 4, 'key' => 'user', 'label' => 'User', 'sort_order' => 4],
]);
```

#### 2. Add department_id to users

```php
// database/migrations/2025_10_27_100001_add_department_id_to_users.php

Schema::table('sys_users', function (Blueprint $table) {
    $table->unsignedBigInteger('department_id')->nullable()->after('email');
    $table->foreign('department_id')->references('id')->on('sys_departments')->onDelete('set null');
});

DB::table('sys_users')->update(['department_id' => 4]); // Default = User
```

#### 3. Add created_by/updated_by to ALL tables

```php
// database/migrations/2025_10_27_100002_add_user_tracking_to_all_tables.php

$tables = [
    'sys_menus', 'sys_menu_groups', 'sys_departments',
    'sys_department_menu_permissions', 'sys_user_menu_permissions',
    'sys_users', 'sys_user_company_access',
    'companies', 'branches', 'cheques', 'cheque_templates',
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        Schema::table($table, function (Blueprint $table) {
            if (!Schema::hasColumn($table->getTable(), 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                $table->foreign('created_by')->references('id')->on('sys_users')->onDelete('set null');
            }
            if (!Schema::hasColumn($table->getTable(), 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
                $table->foreign('updated_by')->references('id')->on('sys_users')->onDelete('set null');
            }
        });
    }
}
```

### ✅ Checklist Phase 1

- [ ] สร้าง migration 100000 (rename department_id)
- [ ] สร้าง migration 100001 (add department_id to users)
- [ ] สร้าง migration 100002 (add created_by/updated_by)
- [ ] สร้าง DepartmentPermissionsSeeder
- [ ] รัน `php artisan migrate`
- [ ] รัน `php artisan db:seed --class=DepartmentPermissionsSeeder`

---

## 📦 Phase 2: ปรับ Model และ Logic

### 🎯 เป้าหมาย

- สร้าง Trait สำหรับ auto-fill `created_by`/`updated_by`
- อัพเดท Models ทั้งหมดให้ใช้ Trait นี้
- เพิ่ม Methods สำหรับตรวจสอบสิทธิ์

### 📝 Trait: HasUserTracking

```php
// app/Traits/HasUserTracking.php

namespace App\Traits;

trait HasUserTracking
{
    protected static function bootHasUserTracking()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
```

### 📝 Models ที่ต้องเพิ่ม Trait

```php
// เพิ่ม use HasUserTracking; ในทุกไฟล์นี้:

- app/Models/Menu.php
- app/Models/User.php
- app/Models/Department.php
- app/Models/Company.php
- app/Models/Branch.php
- app/Models/Cheque.php
- app/Models/ChequeTemplate.php
- app/Models/UserCompanyAccess.php
- app/Models/DepartmentMenuPermission.php
- app/Models/UserMenuPermission.php
- app/Models/MenuGroup.php
```

### 📝 User Model: เพิ่ม Methods

```php
// app/Models/User.php

public function getAccessibleMenus()
{
    if (!$this->department_id) return collect([]);

    $departmentMenuIds = DepartmentMenuPermission::where('department_id', $this->department_id)
        ->pluck('menu_id');

    $userMenuIds = $this->userMenuPermissions()->pluck('menu_id');

    $allMenuIds = $departmentMenuIds->merge($userMenuIds)->unique();

    return Menu::whereIn('id', $allMenuIds)->active()->orderBy('sort_order')->get();
}

public function hasMenuAccess($routeName)
{
    $menu = Menu::where('route', $routeName)->first();
    if (!$menu) return true;

    return $this->getAccessibleMenus()->contains('id', $menu->id);
}

public function getAccessibleCompanies()
{
    return $this->companyAccess()->with('company')->get()->pluck('company');
}

public function hasAccessToCompany($companyId)
{
    return $this->companyAccess()->where('company_id', $companyId)->exists();
}

public function setCurrentCompany($companyId)
{
    if (!$this->hasAccessToCompany($companyId)) return false;

    session(['current_company' => Company::find($companyId)]);
    return true;
}
```

### ✅ Checklist Phase 2

- [ ] สร้าง HasUserTracking trait
- [ ] เพิ่ม trait ให้ทุก Model
- [ ] อัพเดท User model (methods)
- [ ] ทดสอบ create/update → ตรวจสอบ created_by/updated_by

---

## 📦 Phase 3: ปรับหน้าจัดการเมนู

### 🎯 เป้าหมาย

- เปลี่ยน Tab จาก "แผนกต่างๆ" → "ระบบ" และ "Bplus"
- ปรับ JavaScript ให้รองรับ `system_type`

### 📝 JavaScript Changes

```javascript
// เปลี่ยนจาก:
departments: @json($departments),
selectedDepartment: ...

// เป็น:
systemTypes: [
    { id: 1, key: 'system', label: 'ระบบ' },
    { id: 2, key: 'bplus', label: 'Bplus' }
],
selectedSystemType: 1,
```

### 📝 HTML Changes

```html
<!-- เปลี่ยนจาก Department Tabs -->
<button onclick="menuManager.switchDepartment({{ $dept->id }})">

<!-- เป็น System Type Tabs -->
<button onclick="menuManager.switchSystemType(1)">ระบบ</button>
<button onclick="menuManager.switchSystemType(2)">Bplus</button>
```

### ✅ Checklist Phase 3

- [ ] อัพเดท MenuController
- [ ] แก้ไข JavaScript (department → systemType)
- [ ] แก้ไข HTML (tabs)
- [ ] เพิ่ม field system_type ใน Modal Form
- [ ] ทดสอบ CRUD เมนู

---

## 📦 Phase 4: เพิ่มฟีเจอร์เปลี่ยนบริษัท

### 🎯 เป้าหมาย

- เพิ่มเมนู "เปลี่ยนบริษัท" ใน Sidebar (Bplus)
- แสดงโลโก์บริษัทที่ Sidebar Header
- Modal เลือกบริษัท
- เก็บบริษัทปัจจุบันใน Session

### 📝 Files to Create/Edit

1. **Controller**: `app/Http/Controllers/CompanyController.php`
2. **Routes**: `routes/web.php`
3. **View**: `resources/views/components/company-switch-modal.blade.php`
4. **Sidebar**: `resources/views/tailadmin/partials/sidebar.blade.php`
5. **Seeder**: เพิ่มเมนู "เปลี่ยนบริษัท"

### 📝 Sidebar Header

```blade
@if(session('current_company'))
<div class="px-4 py-4 border-b">
    <div class="flex items-center gap-3">
        <img src="{{ session('current_company')->logo ?? '/default-logo.png' }}"
             class="w-12 h-12 rounded-lg">
        <div>
            <div class="text-sm font-semibold">{{ session('current_company')->name }}</div>
            <div class="text-xs text-gray-500">Bplus System</div>
        </div>
    </div>
</div>
@endif
```

### ✅ Checklist Phase 4

- [ ] สร้าง CompanyController
- [ ] เพิ่ม Routes
- [ ] สร้าง Modal component
- [ ] อัพเดท Sidebar (header + เมนู)
- [ ] ทดสอบเปลี่ยนบริษัท

---

## 📦 Phase 5: ปรับหน้าจัดการผู้ใช้

### 🎯 เป้าหมาย

- รวม `admin/users` + `admin/user-approvals` เป็น 1 หน้า 2 tabs
- เพิ่มฟีเจอร์เลือกแผนก
- เพิ่มฟีเจอร์เลือกบริษัทที่เข้าถึง

### 📝 View Structure

```
จัดการผู้ใช้
├── Tab 1: ผู้ใช้ทั้งหมด
│   └── ตาราง: ชื่อ, Email, แผนก, บริษัทที่เข้าถึง, สถานะ, จัดการ
└── Tab 2: รออนุมัติ (badge แสดงจำนวน)
    └── ตาราง: ชื่อ, Email, วันที่สมัคร, [อนุมัติ/ปฏิเสธ]
```

### 📝 User Registration

```php
// เมื่อสมัครใหม่
$user->department_id = 4; // แผนก "User" (default)
$user->is_approved = false; // รออนุมัติ
```

### ✅ Checklist Phase 5

- [ ] สร้าง View `admin/users.blade.php` (2 tabs)
- [ ] อัพเดท UserController
- [ ] เพิ่ม Routes
- [ ] อัพเดท Registration logic
- [ ] ทดสอบทุกฟีเจอร์

---

## 📦 Phase 6: ตั้งค่าสิทธิ์แผนกเริ่มต้น

### 🎯 เป้าหมาย

- Admin เห็นทุกเมนู
- User เห็นแค่เมนูเดียว (Bplus Dashboard)

### 📝 Seeder

```php
// DepartmentPermissionsSeeder.php

// Admin (ID: 3) → ทุกเมนู
foreach (Menu::all() as $menu) {
    DepartmentMenuPermission::create([
        'department_id' => 3,
        'menu_id' => $menu->id
    ]);
}

// User (ID: 4) → Bplus Dashboard
DepartmentMenuPermission::create([
    'department_id' => 4,
    'menu_id' => Menu::where('key', 'bplus_dashboard')->first()->id
]);
```

### ✅ Checklist Phase 6

- [ ] รัน Seeder
- [ ] ตรวจสอบ sys_department_menu_permissions
- [ ] Login เป็น Admin → เห็นทุกเมนู
- [ ] Login เป็น User → เห็น 1 เมนู

---

## 📦 Phase 7: อัพเดท Middleware & Sidebar

### 🎯 เป้าหมาย

- Middleware ตรวจสอบสิทธิ์ตามแผนก
- Sidebar แสดงเฉพาะเมนูที่มีสิทธิ์

### 📝 MenuPermission Middleware

```php
public function handle($request, Closure $next)
{
    $user = auth()->user();
    $routeName = $request->route()->getName();

    $menu = Menu::where('route', $routeName)->first();
    if (!$menu) return $next($request);

    if (!$user->hasMenuAccess($routeName)) {
        return redirect()->route('home')->with('forbidden', 'ไม่มีสิทธิ์');
    }

    return $next($request);
}
```

### 📝 Sidebar

```blade
@php
    $accessibleMenus = auth()->user()->getAccessibleMenus();
    $systemMenus = $accessibleMenus->where('system_type', 1);
    $bplusMenus = $accessibleMenus->where('system_type', 2);
@endphp

@if($systemMenus->count() > 0)
    <div>ระบบ</div>
    @foreach($systemMenus as $menu)
        <!-- แสดงเมนู -->
    @endforeach
@endif

@if($bplusMenus->count() > 0)
    <div>Bplus</div>
    @foreach($bplusMenus as $menu)
        <!-- แสดงเมนู -->
    @endforeach
@endif
```

### ✅ Checklist Phase 7

- [ ] อัพเดท MenuPermission middleware
- [ ] อัพเดท Sidebar
- [ ] สร้าง menu-item component
- [ ] ทดสอบสิทธิ์

---

## 📦 Phase 8: Testing & Cleanup

### ✅ Testing Checklist

#### จัดการเมนู
- [ ] สลับ Tab ระบบ/Bplus
- [ ] เพิ่ม/แก้ไข/ลบเมนู
- [ ] Drag & Drop เปลี่ยนลำดับ
- [ ] Drag & Drop สลับ parent-child
- [ ] Toggle เปิด/ปิด
- [ ] ตรวจสอบ created_by/updated_by

#### เปลี่ยนบริษัท
- [ ] เห็นโลโก์บริษัท
- [ ] เปิด Modal เลือกบริษัท
- [ ] เปลี่ยนบริษัท → รีโหลด
- [ ] Logout → Login → เลือกใหม่

#### จัดการผู้ใช้
- [ ] เห็น 2 tabs
- [ ] แก้ไข user (แผนก + บริษัท)
- [ ] อนุมัติ/ปฏิเสธ
- [ ] สมัครใหม่ → แผนก User

#### สิทธิ์
- [ ] Admin เห็นทุกเมนู
- [ ] User เห็นบางเมนู
- [ ] เข้าหน้าที่ไม่มีสิทธิ์ → redirect

### 🧹 Cleanup

- [ ] ลบ view เก่า (card version)
- [ ] ลบ controller methods ที่ไม่ใช้
- [ ] Clear cache
- [ ] อัพเดท documentation

---

## 📁 ไฟล์ทั้งหมดที่เกี่ยวข้อง

### Migrations (3 ไฟล์ใหม่)
1. `2025_10_27_100000_rename_department_id_to_system_type_in_menus.php`
2. `2025_10_27_100001_add_department_id_to_users.php`
3. `2025_10_27_100002_add_user_tracking_to_all_tables.php`

### Traits (1 ไฟล์ใหม่)
1. `app/Traits/HasUserTracking.php` ⭐

### Models (11 ไฟล์แก้ไข)
1. `app/Models/Menu.php`
2. `app/Models/User.php`
3. `app/Models/Department.php`
4. `app/Models/Company.php`
5. `app/Models/Branch.php`
6. `app/Models/Cheque.php`
7. `app/Models/ChequeTemplate.php`
8. `app/Models/UserCompanyAccess.php`
9. `app/Models/DepartmentMenuPermission.php`
10. `app/Models/UserMenuPermission.php`
11. `app/Models/MenuGroup.php`

### Controllers (3 ไฟล์)
1. `app/Http/Controllers/Admin/MenuController.php` (แก้ไข)
2. `app/Http/Controllers/Admin/UserController.php` (แก้ไข)
3. `app/Http/Controllers/CompanyController.php` ⭐ ใหม่

### Views (5 ไฟล์)
1. `resources/views/admin/menus-simple.blade.php` (แก้ไข)
2. `resources/views/admin/users.blade.php` ⭐ ใหม่
3. `resources/views/tailadmin/partials/sidebar.blade.php` (แก้ไข)
4. `resources/views/tailadmin/partials/menu-item.blade.php` ⭐ ใหม่
5. `resources/views/components/company-switch-modal.blade.php` ⭐ ใหม่

### Seeders (1 ไฟล์ใหม่)
1. `database/seeders/DepartmentPermissionsSeeder.php`

### Middleware (1 ไฟล์แก้ไข)
1. `app/Http/Middleware/MenuPermission.php`

---

## 🚀 คำสั่งที่ต้องรัน

```bash
# 1. Migrations
php artisan migrate

# 2. Seeders
php artisan db:seed --class=DepartmentPermissionsSeeder

# 3. Clear caches
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

---

## ⚠️ ข้อควรระวัง

### 🔥 Breaking Changes
- `sys_menus.department_id` → `sys_menus.system_type`
- `sys_departments` ข้อมูลถูก truncate
- `sys_users` เพิ่ม field `department_id`

### 💾 Backup
- **Backup ฐานข้อมูลก่อน migrate** (สำคัญมาก!)
- Session ของ user จะ reset
- User ต้อง logout และ login ใหม่

### 💡 Tips
- ใช้ Git branch แยก
- Test บน staging ก่อน
- สร้าง user ทดสอบในแต่ละแผนก

---

## 📌 สำคัญ: เรื่อง created_by/updated_by

### ⭐ ทุกตารางต้องมี
```php
$table->unsignedBigInteger('created_by')->nullable();
$table->unsignedBigInteger('updated_by')->nullable();
$table->foreign('created_by')->references('id')->on('sys_users');
$table->foreign('updated_by')->references('id')->on('sys_users');
```

### ⭐ ทุก Model ต้องใช้ Trait
```php
use HasUserTracking;
```

### ⭐ Auto-fill เมื่อ
- Create → `created_by` = current user
- Update → `updated_by` = current user

---

## 📞 เมื่อเริ่ม Session ใหม่

### สิ่งที่ต้องทำ:

1. อ่านเอกสารนี้ทั้งหมด
2. ถาม User ว่าอยู่ Phase ไหน
3. เช็ค Checklist ของ Phase นั้น
4. ดำเนินการต่อตาม Phase

### สิ่งที่ต้องรู้:

- **วัตถุประสงค์**: แยกเมนู 2 ระบบ (System/Bplus)
- **เป้าหมายสำคัญ**: เพิ่ม created_by/updated_by ทุกตาราง
- **คำตอบ User**: อ่านในหัวข้อ "คำตอบจาก User"
- **แผนการ**: 8 Phases ตามลำดับ

---

**🎉 สร้างโดย AI Assistant - 27 ตุลาคม 2025**
