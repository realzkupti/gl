# 📋 คู่มือการอัปเกรดระบบสิทธิ์และเมนู

## สรุปการเปลี่ยนแปลง

ระบบได้รับการปรับปรุงครั้งใหญ่ โดยเปลี่ยนจาก **Role-based Permissions** เป็น **Department-based Permissions**

---

## 🔄 การเปลี่ยนแปลงหลัก

### 1. ชื่อตาราง (Tables)

| เดิม | ใหม่ | หมายเหตุ |
|------|------|----------|
| `menu_groups` | `sys_departments` | เปลี่ยนชื่อและความหมาย |
| `users` | `sys_users` | เพิ่ม prefix sys_ |
| `menus` | `sys_menus` | เพิ่ม prefix sys_ |
| `user_menu_permissions` | `sys_user_menu_permissions` | เพิ่ม prefix sys_ |
| `companies` | `sys_companies` | เพิ่ม prefix sys_ |
| `activity_logs` | `sys_activity_logs` | เพิ่ม prefix sys_ |
| `cheques` | `sys_cheques` | เพิ่ม prefix sys_ |
| `cheque_templates` | `sys_cheque_templates` | เพิ่ม prefix sys_ |
| `branches` | `sys_branches` | เพิ่ม prefix sys_ |
| `roles` | ❌ ลบออก | ไม่ใช้แล้ว |
| `role_menu_permissions` | ❌ ลบออก | ไม่ใช้แล้ว |
| `user_roles` | ❌ ลบออก | ไม่ใช้แล้ว |

### 2. ตารางใหม่

| ตาราง | ความหมาย |
|-------|----------|
| `sys_department_menu_permissions` | สิทธิ์เมนูของแต่ละแผนก |
| `sys_user_company_access` | ผู้ใช้เข้าถึง Company ไหนได้บ้าง |

### 3. คอลัมน์ใหม่

| ตาราง | คอลัมน์ | ความหมาย |
|-------|---------|----------|
| `sys_users` | `department_id` | แผนกของผู้ใช้ (1:1) |
| `sys_menus` | `connection_type` | pgsql หรือ company |
| `sys_menus` | `department_id` | เดิมคือ menu_group_id |

---

## 🎯 ลำดับสิทธิ์ใหม่

**เดิม:**
1. Admin override (admin@local)
2. User-specific permissions
3. Role permissions
4. Deny

**ใหม่:**
1. Admin override (admin@local)
2. User-specific permissions (override)
3. **Department permissions** ⭐ ใหม่
4. Deny

---

## 📂 ไฟล์ที่เปลี่ยนแปลง

### Models (11 ไฟล์)
- ✅ `User.php` - เพิ่ม department relationship
- ✅ `Department.php` - ใหม่ (เดิมคือ MenuGroup)
- ✅ `Menu.php` - ใช้ department_id
- ✅ `Company.php` - ใช้ sys_companies
- ✅ `UserMenuPermission.php` - ใช้ sys_*
- ✅ `DepartmentMenuPermission.php` - ใหม่
- ✅ `UserCompanyAccess.php` - ใหม่
- ✅ `ActivityLog.php` - ใช้ sys_*
- ✅ `Cheque.php`, `Branch.php`, `ChequeTemplate.php` - ใช้ sys_*

### Controllers (4 ไฟล์)
- ✅ `DepartmentController.php` - ใหม่
- ✅ `DepartmentPermissionController.php` - ใหม่
- ✅ `UserPermissionController.php` - เปลี่ยนจาก role เป็น department
- ✅ `MenuController.php` - ใช้ department_id

### Middleware & Support
- ✅ `MenuPermission.php` - ใช้ department แทน role
- ✅ `Perm.php` - ใช้ department แทน role

### Routes
- ✅ เพิ่ม routes สำหรับ Department
- ✅ เพิ่ม routes สำหรับ DepartmentPermission
- ❌ ลบ RoleController

---

## 🚀 วิธีการอัปเกรด

### ขั้นตอนที่ 1: Run Migrations

```bash
# Migration ทั้งหมดจะ run ตามลำดับ timestamp
php artisan migrate
```

Migrations ที่จะถูก run:
1. `2025_10_25_150000_rename_tables_with_sys_prefix.php`
2. `2025_10_25_151000_add_department_support.php`
3. `2025_10_25_152000_create_user_company_access.php`
4. `2025_10_25_153000_seed_bplus_department_and_menus.php`

### ขั้นตอนที่ 2: Run Seeders

```bash
# 1. สร้างแผนก (Departments)
php artisan db:seed --class=DepartmentSeeder

# 2. สร้างเมนูระบบ
php artisan db:seed --class=SystemMenusSeeder
```

### ขั้นตอนที่ 3: กำหนดแผนกให้ผู้ใช้

```sql
-- ตัวอย่าง: กำหนดผู้ใช้ทั้งหมดให้อยู่ในแผนก 'system'
UPDATE sys_users
SET department_id = (SELECT id FROM sys_departments WHERE key = 'system')
WHERE department_id IS NULL;
```

### ขั้นตอนที่ 4: กำหนดสิทธิ์แผนก

เข้าไปที่:
- **จัดการแผนก**: `/admin/departments`
- **กำหนดสิทธิ์แผนก**: `/admin/department-permissions`

---

## 📊 โครงสร้างแผนก (Departments)

### แผนกที่สร้างโดย Seeder

| Key | Label | ความหมาย |
|-----|-------|----------|
| `system` | เมนูระบบ | เช็ค, จัดการผู้ใช้, จัดการสิทธิ์ |
| `bplus` | Bplus | งบทดลอง, ผังบัญชี, สมุดรายวัน |
| `demo` | Demo Components | หน้าตัวอย่าง UI |

---

## 🔐 ระบบสิทธิ์ใหม่

### การกำหนดสิทธิ์ 2 ระดับ

**1. สิทธิ์แผนก (Department Permissions)**
- ผู้ใช้ทุกคนในแผนกจะได้สิทธิ์เหมือนกัน
- ตั้งค่าที่: `/admin/department-permissions`

**2. สิทธิ์รายบุคคล (User-specific Permissions)**
- Override สิทธิ์แผนก
- ตั้งค่าที่: `/admin/user-permissions/{userId}`

### สิทธิ์การเข้าถึง Company

**ตาราง `sys_user_company_access`**
- กำหนดว่าผู้ใช้เข้าถึง Company (ฐานข้อมูล) ไหนได้บ้าง
- ถ้าไม่มี record = ไม่จำกัด (เข้าได้ทุก Company)

---

## 🎨 เมนู Bplus

### เมนูใหม่ที่เพิ่มเข้ามา

1. **งบทดลอง** - `bplus_trial_balance`
2. **ผังบัญชี** - `bplus_chart_of_accounts`
3. **สมุดรายวันทั่วไป** - `bplus_general_journal`
4. **สมุดบัญชีแยกประเภท** - `bplus_general_ledger`
5. **งบการเงิน** - `bplus_financial_statements`
6. **รายงาน** - `bplus_reports`
7. **การตั้งค่า Bplus** - `bplus_companies`

### Connection Type

- **pgsql**: เมนูที่ใช้ PostgreSQL เสมอ (เช็ค, จัดการผู้ใช้)
- **company**: เมนูที่ต้องสลับ database ตาม Company (Bplus)

---

## ⚠️ Breaking Changes

### 1. ไฟล์ที่ถูกลบ
- `app/Http/Controllers/Admin/RoleController.php`
- `app/Models/Role.php`
- `app/Models/RoleMenuPermission.php`

### 2. Routes ที่เปลี่ยนแปลง
- ❌ `/admin/roles` - ลบออก
- ✅ `/admin/departments` - ใหม่
- ✅ `/admin/department-permissions` - ใหม่

### 3. Views ที่ต้องอัปเดต (ถ้ามี)
- `admin/user-permissions-edit.blade.php` - ต้องแสดง department แทน role
- Sidebar - ต้องใช้ `Perm::getUserMenus()` แทน

---

## 🧪 การทดสอบ

### Test Cases

```bash
# 1. ทดสอบ login
- เข้าสู่ระบบด้วย admin@local
- ตรวจสอบว่าเห็นเมนูทั้งหมด

# 2. ทดสอบสิทธิ์แผนก
- สร้างแผนกใหม่
- กำหนดสิทธิ์ให้แผนก
- สร้าง user ในแผนกนั้น
- ตรวจสอบว่า user เห็นเมนูตามสิทธิ์แผนก

# 3. ทดสอบ override
- กำหนดสิทธิ์รายบุคคล (override แผนก)
- ตรวจสอบว่า user เห็นเมนูตามสิทธิ์ส่วนตัว

# 4. ทดสอบ Company Access
- สร้าง Company ใน /admin/companies
- กำหนดสิทธิ์เข้าถึง Company ให้ user
- ตรวจสอบว่าสลับ Company ได้
```

---

## 📞 ติดปัญหา?

### ปัญหาที่พบบ่อย

**Q: Migration ไม่ผ่าน?**
A: ตรวจสอบว่ามี table `menu_groups`, `users`, `menus` อยู่จริง

**Q: Seeder ไม่ทำงาน?**
A: Run `php artisan migrate` ก่อน แล้วค่อย run seeder

**Q: User ไม่เห็นเมนู?**
A: ตรวจสอบว่า user มี `department_id` และแผนกมีสิทธิ์เมนูนั้น

**Q: Error "Table not found"?**
A: ตรวจสอบว่า run migrations ครบทั้ง 4 ไฟล์

---

## ✅ Checklist

- [ ] Run migrations ทั้งหมด
- [ ] Run DepartmentSeeder
- [ ] Run SystemMenusSeeder
- [ ] กำหนด department_id ให้ users ทั้งหมด
- [ ] กำหนดสิทธิ์แผนกอย่างน้อย 1 แผนก
- [ ] ทดสอบ login และดูเมนู
- [ ] ทดสอบการสลับ Company

---

**🎉 ระบบพร้อมใช้งาน!**

สร้างโดย Claude Code - 2025-10-25
