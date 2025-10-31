# 🤖 AI Coding Standards & Style Guide
## Laravel GL Dashboard Project

> **สำหรับ AI Agents**: อ่านเอกสารนี้ก่อนทำงานกับโปรเจคนี้เพื่อให้โค้ดมี pattern เดียวกัน

---

## 🚨 Critical System Architecture (อ่านก่อนเสมอ!)

### Dual-Database System
ระบบนี้ใช้ **2 Database แยกกัน**:

1. **PostgreSQL (`pgsql`)** - Main System
   - Users, Roles, Menus, Permissions
   - **Cheque System** (ระบบส่วนตัวของ owner)
   - Connection: `pgsql` (default)

2. **SQL Server (`sqlsrv`)** - Multi-Company ERP
   - Trial Balance, GL, Multi-company accounting
   - Dynamic connections: `company_a`, `company_b`, etc.
   - Middleware `company.connection` เปลี่ยน default connection

### ⚠️ สำคัญสุด!
- **ระบบเช็ค** ใช้ PostgreSQL เท่านั้น → **ห้าม** ใส่ middleware `company.connection`
- Models ที่ใช้ PostgreSQL ต้อง override `getConnectionName()` เพื่อป้องกัน middleware เปลี่ยน connection
- ใช้ `cmd /c "php artisan ..."` แทนคำสั่งตรงใน PowerShell (หลีกเลี่ยง encoding error)

---

## 📋 Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture & Stack](#architecture--stack)
3. [Database Patterns](#database-patterns)
4. [API & AJAX Patterns](#api--ajax-patterns)
5. [Frontend Patterns](#frontend-patterns)
6. [Security Standards](#security-standards)
7. [File Organization](#file-organization)
8. [Naming Conventions](#naming-conventions)
9. [Code Examples](#code-examples)

---

## 🎯 Project Overview

**Project Name**: GL Dashboard (General Ledger Dashboard)  
**Type**: Laravel 11 + TailAdmin UI + PostgreSQL  
**Language**: Thai (UI), English (Code)  
**Purpose**: Multi-company accounting system with dynamic menu permissions

### Key Features
- Multi-company support with dynamic database switching
- Role-based + User-specific menu permissions
- Database-driven menu groups and navigation
- Cheque printing system
- Trial balance reporting

---

## 🏗️ Architecture & Stack

### System Architecture Overview
**ระบบนี้มี 2 ส่วนหลัก ที่ใช้ Database แยกกัน:**

#### 1. ระบบหลัก (Main System) - PostgreSQL
- **Database**: PostgreSQL (`pgsql` connection)
- **Purpose**: ระบบจัดการผู้ใช้, สิทธิ์, เมนู, และระบบเช็คส่วนตัว
- **Tables**: `users`, `menus`, `menu_groups`, `roles`, `role_menu_permissions`, `user_menu_permissions`, `cheques`, `cheque_templates`, `branches`
- **Connection Name**: `pgsql` (default connection)

#### 2. ระบบ Multi-Company ERP - SQL Server
- **Database**: SQL Server (`sqlsrv` connections)
- **Purpose**: ระบบบัญชีหลายบริษัท (Trial Balance, GL, etc.)
- **Dynamic Connections**: `company_a`, `company_b`, etc. (สร้างตาม `config/companies.json`)
- **Service**: `CompanyManager::class` - จัดการการเปลี่ยนบริษัท
- **Middleware**: `company.connection` - เปลี่ยน default connection เป็น company ที่เลือก

⚠️ **สำคัญมาก**: 
- **ระบบเช็ค** ใช้ PostgreSQL เท่านั้น (เป็นระบบส่วนตัวของ owner)
- **ห้าม** ใส่ middleware `company.connection` ใน routes ที่เกี่ยวกับเช็ค
- Models ที่ใช้ PostgreSQL ต้อง override `getConnectionName()`:
  ```php
  public function getConnectionName()
  {
      return 'pgsql'; // Force PostgreSQL connection
  }
  ```

### Backend
- **Framework**: Laravel 11+
- **Primary Database**: PostgreSQL (main system)
- **Secondary Database**: SQL Server (multi-company ERP)
- **Authentication**: Laravel Fortify
- **Real-time**: Livewire 3 (Volt single-file components)

### Frontend
- **UI Framework**: TailAdmin (Tailwind CSS)
- **Icons**: Heroicons
- **JavaScript**: Vanilla JS (prefer fetch API over axios/ajax)
- **Date Picker**: Flatpickr
- **Alerts**: SweetAlert2

### Database Connection Patterns

#### ระบบหลัก (PostgreSQL)
```php
// Models ที่ใช้ PostgreSQL เท่านั้น
class Cheque extends Model
{
    protected $connection = 'pgsql';
    
    // Override เพื่อป้องกัน middleware เปลี่ยน connection
    public function getConnectionName()
    {
        return 'pgsql';
    }
}

// Query โดยตรง
DB::connection('pgsql')->table('cheques')->get();
Schema::connection('pgsql')->hasTable('cheques');
```

#### ระบบ Multi-Company (SQL Server)
```php
// ใช้ CompanyManager สำหรับเปลี่ยน company
use App\Services\CompanyManager;

$currentCompany = CompanyManager::getSelectedKey(); // 'company_a'
$connection = config('database.default'); // หลังจาก middleware จะเป็น 'company_a'

// Models ที่ใช้ company database
class TrialBalance extends Model
{
    // ไม่ต้องระบุ connection (ใช้ default ที่ middleware กำหนด)
}
```

#### Routes & Middleware
```php
// ระบบเช็ค - ห้ามใส่ company.connection
Route::middleware(['auth', 'menu:cheque,view'])->group(function () {
    Route::get('/cheque/print', [TailAdminController::class, 'chequePrint']);
});

// ระบบ Multi-Company - ใส่ company.connection
Route::middleware(['auth', 'company.connection', 'menu:trial-balance,view'])->group(function () {
    Route::get('/trial-balance', [TailAdminController::class, 'trialBalance']);
});
```

---

## 💾 Database Patterns

### Table Naming
- **Snake case, plural**: `menu_groups`, `role_menu_permissions`
- **Pivot tables**: `{model1}_{model2}` (alphabetically)

### Migration Pattern
```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id();
    $table->string('key', 100)->unique(); // Unique identifier
    $table->string('label'); // Display name (Thai)
    $table->foreignId('parent_id')->nullable()->constrained('table_name')->onDelete('cascade');
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_default')->default(false); // For system records
    $table->timestamps();
    
    // Indexes
    $table->index('sort_order');
    $table->index('is_active');
});
```

### Model Pattern
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuGroup extends Model
{
    protected $fillable = [
        'key',
        'label',
        'sort_order',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_group_id');
    }
}
```

---

## 🌐 API & AJAX Patterns

### ⚠️ CRITICAL: Always Use AJAX/Fetch for Forms
**Why?** เมื่อ form submit แบบ PHP traditional และเกิด validation error:
- ❌ ข้อมูลที่ user กรอกไว้จะหายหมด
- ❌ ต้องกรอกใหม่ทั้งหมด = UX แย่

**Solution:** ใช้ fetch API + JSON response เสมอ

### Controller Pattern (Dual Support)

```php
class MenuController extends Controller
{
    // Traditional (for backward compatibility)
    public function store(Request $request)
    {
        $data = $request->validate([...]);
        Menu::create($data);
        return redirect()->route('admin.menus')->with('status', 'สำเร็จ');
    }

    // ✅ API (Recommended) - JSON response for AJAX
    public function storeApi(Request $request)
    {
        $data = $request->validate([...]);
        
        try {
            $menu = Menu::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'บันทึกสำเร็จ',
                'data' => $menu
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }
}
```

### Routes Pattern
```php
// API routes MUST come BEFORE traditional routes (Laravel routing priority)
Route::get('admin/menus/list', [MenuController::class, 'list']); // ✅ Specific first
Route::post('admin/menus/api', [MenuController::class, 'storeApi']);
Route::put('admin/menus/api/{id}', [MenuController::class, 'updateApi']);
Route::delete('admin/menus/api/{id}', [MenuController::class, 'destroyApi']);

// Traditional routes (fallback)
Route::get('admin/menus', [MenuController::class, 'index']); // ❌ Generic last
Route::post('admin/menus', [MenuController::class, 'store']);
```

### JavaScript Fetch Pattern

```javascript
// ✅ ALWAYS use this pattern for forms

// 1. Configuration
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const API_BASE = '/api';

// 2. Loading state
function showLoading() {
    document.getElementById('loading-overlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

// 3. Save function with error handling
async function saveData() {
    const formData = {
        key: document.getElementById('input-key').value.trim(),
        label: document.getElementById('input-label').value.trim(),
        // ... other fields
    };

    // Client-side validation
    if (!formData.key || !formData.label) {
        showToast('กรุณากรอกข้อมูลให้ครบ', 'warning');
        return;
    }

    try {
        showLoading();
        
        const response = await fetch(`${API_BASE}/endpoint`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN // ⚠️ CRITICAL for POST/PUT/DELETE
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (!response.ok) {
            // Show validation errors
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                showToast(errorMessages, 'error');
            }
            return;
        }

        showToast(data.message || 'บันทึกสำเร็จ', 'success');
        resetForm();
        loadData(); // Refresh list
        
    } catch (error) {
        console.error('Error:', error);
        showToast('เกิดข้อผิดพลาด', 'error');
    } finally {
        hideLoading();
    }
}
```

### Toast Notification Pattern (SweetAlert2)
```javascript
function showToast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type, // 'success', 'error', 'warning', 'info'
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
```

---

## 🎨 Frontend Patterns

### Form Structure (AJAX-ready)
```html
<form id="menu-form" onsubmit="return false;">
    <!-- ❌ NO action, NO method attributes -->
    <input type="hidden" id="form-action" value="create">
    <input type="hidden" id="record-id" value="">

    <!-- Form fields -->
    <input type="text" id="input-key" required
        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />

    <!-- Buttons -->
    <button type="button" onclick="saveData()" class="...">บันทึก</button>
    <button type="button" onclick="resetForm()" class="...">ล้างฟอร์ม</button>
</form>
```

### Loading Overlay
```html
<!-- Add to layout -->
<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="spinner"></div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
```

### Date Input Pattern (Flatpickr)
```javascript
// ❌ DON'T use HTML5 <input type="date"> (ปัญหา dark mode)

// ✅ DO use Flatpickr
flatpickr("#date", {
    dateFormat: "d/m/Y", // 25/10/2025
    defaultDate: new Date(),
    locale: {
        firstDayOfWeek: 1,
        weekdays: {
            shorthand: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
            longhand: ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'],
        },
        months: {
            shorthand: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
            longhand: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
        },
    },
    onChange: function(selectedDates, dateStr, instance) {
        // Handle change
    }
});
```

---

## 🔒 Security Standards

### CSRF Protection
```html
<!-- In <head> of layout -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

```javascript
// In all POST/PUT/DELETE requests
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
}
```

### Admin Authorization Pattern
```php
class MenuController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (!Auth::check() || (Auth::user()->email ?? '') !== 'admin@local') {
            abort(403, 'Forbidden');
        }
    }

    public function index()
    {
        $this->ensureAdmin(); // ⚠️ Call first in every method
        // ...
    }
}
```

---

## 📁 File Organization

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   ├── MenuController.php          # Traditional + API methods
│   │   └── MenuGroupsController.php
│   ├── ChequeApiController.php
│   └── TailAdminController.php
├── Models/
│   ├── Menu.php
│   ├── MenuGroup.php
│   └── User.php
├── Services/
│   └── CompanyManager.php              # Multi-company logic
└── Support/
    └── Perm.php                        # Permission helper

resources/views/
├── admin/
│   ├── menus.blade.php                 # ❌ Traditional (old)
│   └── menus-ajax.blade.php            # ✅ AJAX (recommended)
└── tailadmin/
    ├── layouts/
    │   ├── app.blade.php               # With sidebar
    │   └── print.blade.php             # Without sidebar
    └── pages/
        ├── admin/
        └── cheque/

database/
├── migrations/
└── seeders/
```

---

## 📝 Naming Conventions

### PHP
- **Classes**: PascalCase - `MenuController`, `MenuGroup`
- **Methods**: camelCase - `getUserMenus()`, `storeApi()`
- **Variables**: camelCase - `$menuGroups`, `$isActive`

### Database
- **Tables**: snake_case, plural - `menu_groups`, `users`
- **Columns**: snake_case - `menu_group_id`, `is_active`

### JavaScript
- **Functions**: camelCase - `saveMenu()`, `loadData()`
- **Constants**: UPPER_SNAKE_CASE - `API_BASE`, `CSRF_TOKEN`
- **DOM IDs**: kebab-case - `input-key`, `loading-overlay`

### Routes
```php
// Format: {area}.{resource}.{action}
Route::get('admin/menus', ...)->name('admin.menus');
Route::post('admin/menus/api', ...)->name('admin.menus.store.api');
```

---

## 💡 Code Examples

### Complete CRUD Pattern

#### 1. Migration
```php
// database/migrations/2025_xx_xx_create_items_table.php
Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2)->default(0);
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### 2. Model
```php
// app/Models/Item.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'price', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];
}
```

#### 3. Controller
```php
// app/Http/Controllers/ItemController.php
namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // API List
    public function list()
    {
        $items = Item::orderBy('sort_order')->get();
        return response()->json(['success' => true, 'items' => $items]);
    }

    // API Create
    public function storeApi(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:items,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        try {
            $item = Item::create($data);
            return response()->json([
                'success' => true,
                'message' => 'บันทึกสำเร็จ',
                'item' => $item
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    // API Update
    public function updateApi(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:items,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $item->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'อัปเดตสำเร็จ',
            'item' => $item
        ]);
    }

    // API Delete
    public function destroyApi($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบสำเร็จ'
        ]);
    }
}
```

#### 4. Routes
```php
// routes/web.php
Route::middleware(['auth'])->group(function() {
    // API routes (specific paths first)
    Route::get('items/list', [ItemController::class, 'list'])->name('items.list');
    Route::post('items/api', [ItemController::class, 'storeApi'])->name('items.store.api');
    Route::put('items/api/{id}', [ItemController::class, 'updateApi'])->name('items.update.api');
    Route::delete('items/api/{id}', [ItemController::class, 'destroyApi'])->name('items.destroy.api');
});
```

#### 5. View
```html
<!-- resources/views/items/index.blade.php -->
@extends('tailadmin.layouts.app')

@section('title', 'จัดการสินค้า')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white mb-6">จัดการสินค้า</h2>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Form -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 id="form-title" class="text-lg font-semibold mb-4">เพิ่มสินค้าใหม่</h3>

            <form id="item-form" onsubmit="return false;" class="space-y-4">
                <input type="hidden" id="form-action" value="create">
                <input type="hidden" id="item-id" value="">

                <div>
                    <label class="mb-2 block text-sm font-medium">รหัสสินค้า <span class="text-red-500">*</span></label>
                    <input type="text" id="input-code" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">ชื่อสินค้า <span class="text-red-500">*</span></label>
                    <input type="text" id="input-name" required class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">ราคา</label>
                    <input type="number" id="input-price" step="0.01" value="0" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white" />
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="saveItem()" class="flex-1 rounded-lg bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">บันทึก</button>
                    <button type="button" onclick="resetForm()" class="rounded-lg border border-gray-300 px-6 py-2.5 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">ล้าง</button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold mb-4">รายการสินค้า</h3>
            <div id="item-list"></div>
        </div>
    </div>
</div>

<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="spinner"></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function showLoading() { document.getElementById('loading-overlay').style.display = 'flex'; }
function hideLoading() { document.getElementById('loading-overlay').style.display = 'none'; }

function showToast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

async function loadItems() {
    try {
        showLoading();
        const response = await fetch('/items/list', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        renderItems(data.items);
    } catch (error) {
        showToast('ไม่สามารถโหลดข้อมูลได้', 'error');
    } finally {
        hideLoading();
    }
}

async function saveItem() {
    const formAction = document.getElementById('form-action').value;
    const itemId = document.getElementById('item-id').value;
    
    const formData = {
        code: document.getElementById('input-code').value.trim(),
        name: document.getElementById('input-name').value.trim(),
        price: parseFloat(document.getElementById('input-price').value) || 0,
        is_active: true
    };

    if (!formData.code || !formData.name) {
        showToast('กรุณากรอกข้อมูลให้ครบ', 'warning');
        return;
    }

    try {
        showLoading();
        const url = formAction === 'create' ? '/items/api' : `/items/api/${itemId}`;
        const method = formAction === 'create' ? 'POST' : 'PUT';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                showToast(Object.values(data.errors).flat().join('\n'), 'error');
            }
            return;
        }

        showToast(data.message, 'success');
        resetForm();
        loadItems();
    } catch (error) {
        showToast('เกิดข้อผิดพลาด', 'error');
    } finally {
        hideLoading();
    }
}

function renderItems(items) {
    const container = document.getElementById('item-list');
    container.innerHTML = items.map(item => `
        <div class="rounded-lg border p-4 mb-2">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold">${item.name}</h4>
                    <p class="text-sm text-gray-600">${item.code} - ฿${parseFloat(item.price).toFixed(2)}</p>
                </div>
                <div class="flex gap-2">
                    <button onclick='editItem(${JSON.stringify(item)})' class="text-blue-600 hover:text-blue-800">แก้ไข</button>
                    <button onclick="deleteItem(${item.id}, '${item.name}')" class="text-red-600 hover:text-red-800">ลบ</button>
                </div>
            </div>
        </div>
    `).join('');
}

function editItem(item) {
    document.getElementById('form-title').textContent = 'แก้ไขสินค้า';
    document.getElementById('form-action').value = 'update';
    document.getElementById('item-id').value = item.id;
    document.getElementById('input-code').value = item.code;
    document.getElementById('input-name').value = item.name;
    document.getElementById('input-price').value = item.price;
}

function resetForm() {
    document.getElementById('form-title').textContent = 'เพิ่มสินค้าใหม่';
    document.getElementById('form-action').value = 'create';
    document.getElementById('item-form').reset();
}

async function deleteItem(id, name) {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `ต้องการลบ "${name}" หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    });

    if (!result.isConfirmed) return;

    try {
        showLoading();
        const response = await fetch(`/items/api/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const data = await response.json();
        showToast(data.message, 'success');
        loadItems();
    } catch (error) {
        showToast('ไม่สามารถลบได้', 'error');
    } finally {
        hideLoading();
    }
}

document.addEventListener('DOMContentLoaded', loadItems);
</script>
@endpush
```

---

## 🎯 Best Practices Summary

### ✅ DO
- Use fetch API for all form submissions
- Include CSRF token in all POST/PUT/DELETE requests
- Show loading overlay during API calls
- Use SweetAlert2 for confirmations and notifications
- Validate on both client and server side
- Keep user input on validation errors (AJAX advantage)
- Use Flatpickr for date inputs
- Add `!important` to CSS when inline styles might override
- **Override `getConnectionName()`** in models ที่ใช้ PostgreSQL เพื่อป้องกัน middleware เปลี่ยน connection
- ใช้ `cmd /c` แทนคำสั่งตรงใน PowerShell เพื่อหลีกเลี่ยง encoding error (แ นำหน้า)

### ❌ DON'T
- Use traditional form submissions (loses data on error)
- Forget CSRF tokens in fetch requests
- Use HTML5 `<input type="date">` (dark mode issues)
- Mix axios/ajax with fetch (consistency)
- Hardcode database names (use CompanyManager)
- Delete records with `is_default` or `is_system` flags
- **ห้าม** ใส่ middleware `company.connection` ใน routes ที่ใช้ PostgreSQL (เช่น cheque, menus, users)
- **ห้าม** hardcode `DB::connection('pgsql')` ใน controller (ให้ใช้ model แทน)

---

## 🐛 Common Issues & Troubleshooting

### Issue 1: Wrong Database Connection
**Symptom**: `SQLSTATE[42S02]: Base table or view not found` หรือ `Connection: company_xxx` แต่ควรใช้ `pgsql`

**Root Cause**: Middleware `company.connection` เปลี่ยน default connection เป็น SQL Server

**Solution**:
```php
// 1. เอา middleware ออกจาก routes
Route::middleware(['auth', 'menu:cheque,view'])->group(function () {
    // ✅ ไม่มี 'company.connection'
});

// 2. Override getConnectionName() ใน Model
class Cheque extends Model
{
    protected $connection = 'pgsql';
    
    public function getConnectionName()
    {
        return 'pgsql'; // Force PostgreSQL
    }
}
```

### Issue 2: Missing Database Columns
**Symptom**: `SQLSTATE[42703]: Undefined column` (PostgreSQL) หรือ `SQLSTATE[42S22]` (SQL Server)

**Root Cause**: Migration มี `$table->timestamps()` แต่ table จริงไม่มี `created_at`, `updated_at`

**Solution**:
```php
// สร้าง migration ใหม่แบบปลอดภัย
Schema::connection('pgsql')->table('cheques', function (Blueprint $table) use ($schema) {
    if (!$schema->hasColumn('cheques', 'created_at')) {
        $table->timestamp('created_at')->nullable()->default(now());
    }
    if (!$schema->hasColumn('cheques', 'updated_at')) {
        $table->timestamp('updated_at')->nullable()->default(now());
    }
});

// Run: cmd /c "php artisan migrate --database=pgsql"
```

### Issue 3: PowerShell Encoding Error
**Symptom**: `แphp : The term 'แphp' is not recognized...`

**Root Cause**: PowerShell มีปัญหา character encoding ใส่ แ นำหน้าคำสั่ง

**Solution**:
```powershell
# ❌ ห้ามใช้แบบนี้
php artisan migrate

# ✅ ใช้ cmd /c แทน
cmd /c "php artisan migrate --database=pgsql"
```

### Issue 4: Date Format Mismatch
**Symptom**: Validation error `The date does not match the format Y-m-d`

**Root Cause**: Flatpickr ใช้ format `d/m/Y` (25/10/2025) แต่ Laravel ต้องการ `Y-m-d` (2025-10-25)

**Solution**:
```javascript
// แปลงวันที่ก่อน submit
function convertDateFormat(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('/');
    if (parts.length !== 3) return dateStr;
    return `${parts[2]}-${parts[1]}-${parts[0]}`; // d/m/Y → Y-m-d
}

const formData = {
    date: convertDateFormat(formElement.date.value)
};
```

### Issue 5: Print Page Showing Blank
**Symptom**: Print preview แสดงหน้าว่าง, ตรวจสอบแล้วมี CSS `visibility: hidden`

**Root Cause**: `@media print` ใช้ `visibility: visible !important` แต่ element parent มี `display: none`

**Solution**:
```css
/* ❌ ไม่ Work */
.print-only { visibility: hidden; }
@media print {
    .print-only { visibility: visible !important; }
}

/* ✅ Work */
.print-only { display: none; }
@media print {
    .print-only { display: block !important; }
}
```

---

## 📚 Reference Files

When working on similar features, refer to these files:

### Complete AJAX CRUD Example
- **View**: `resources/views/admin/menus.blade.php` (converted to AJAX)
- **Controller**: `app/Http/Controllers/ChequeApiController.php` (MVC pattern)
- **Routes**: `routes/web.php` (lines 97-117 for cheque API routes)

### Date Picker Implementation
- **File**: `resources/views/tailadmin/pages/cheque/print.blade.php`
- **Lines**: 430-480 (initializeDatePicker function)

### Database Connection Examples
- **PostgreSQL Model**: `app/Models/Cheque.php` (with `getConnectionName()` override)
- **Multi-Company Service**: `app/Services/CompanyManager.php`
- **Safe Migration**: `database/migrations/2025_10_25_045955_add_missing_columns_to_cheques_table.php`

### Permission System
- **Helper**: `app/Support/Perm.php`
- **Method**: `getUserMenus()` - combines role + user permissions

---

## 🔄 Version History

- **v1.2** (2025-10-25): Added Troubleshooting section
  - Common database connection issues
  - PowerShell encoding workarounds
  - Date format conversion patterns
  - Print CSS patterns
- **v1.1** (2025-10-25): Enhanced Architecture section
  - Documented dual-database system (PostgreSQL + SQL Server)
  - Middleware behavior and connection override patterns
  - Routes organization for different database systems
- **v1.0** (2025-10-25): Initial AI style guide created
  - AJAX/Fetch patterns established
  - Database conventions documented
  - Complete CRUD example added

---

## 📞 Questions?

If you encounter a pattern not covered here:
1. Check existing similar features in the codebase
2. Follow the principle: **"Data should never be lost on error"**
3. Use fetch API + JSON responses
4. Maintain consistency with existing code
5. **Check troubleshooting section** สำหรับ common issues

---

**Last Updated**: October 25, 2025  
**Maintainer**: Development Team  
**For**: AI Agents & Human Developers

