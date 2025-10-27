<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $this->ensureAdmin();

        // Use Model instead of Query Builder
        $menus = Menu::orderBy('sort_order')->orderBy('id')->get();

        // Render the fully JS-driven view
        return view('admin.menus', [
            'menus' => $menus,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'key' => 'required|string|max:100|unique:pgsql.sys_menus,key',
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:pgsql.sys_menus,id',
            'sort_order' => 'nullable|integer',
            'department_id' => 'nullable|integer|exists:pgsql.sys_departments,id',
            'connection_type' => 'nullable|string|in:pgsql,company',
        ]);

        // Use Model to create menu
        Menu::create([
            'key' => $data['key'],
            'label' => $data['label'],
            'route' => $data['route'] ?? null,
            'icon' => $data['icon'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'department_id' => $data['department_id'] ?? null,
            'connection_type' => $data['connection_type'] ?? 'pgsql',
            'is_active' => true,
        ]);

        return redirect()->route('admin.menus')->with('status', 'บันทึกเมนูแล้ว');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string|max:100|unique:pgsql.sys_menus,key,' . $id,
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:pgsql.menus,id',
            'sort_order' => 'nullable|integer',
            'menu_group_id' => 'nullable|integer|exists:pgsql.menu_groups,id',
            'is_active' => 'boolean',
        ]);

        $menu->update($data);

        return redirect()->route('admin.menus')->with('status', 'อัปเดตเมนูแล้ว');
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $menu = Menu::findOrFail($id);

        // Prevent deletion of system menus
        if ($menu->is_system) {
            return redirect()->route('admin.menus')
                ->with('error', 'ไม่สามารถลบเมนูระบบได้ (System Menu)');
        }

        $menu->delete();

        return redirect()->route('admin.menus')->with('status', 'ลบเมนูแล้ว');
    }

    public function toggle($id)
    {
        $this->ensureAdmin();

        $menu = Menu::findOrFail($id);
        $menu->is_active = !$menu->is_active;
        $menu->save();

        $status = $menu->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
        return redirect()->route('admin.menus')->with('status', "เปลี่ยนสถานะเมนู '{$menu->label}' เป็น {$status} แล้ว");
    }

    // ==================== API Methods (JSON Response) ====================

    public function list(Request $request)
    {
        $this->ensureAdmin();

        $menus = Menu::orderBy('sort_order')->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'menus' => $menus
        ]);
    }

    public function storeApi(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'key' => 'required|string|max:100|unique:pgsql.sys_menus,key',
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:pgsql.sys_menus,id',
            'sort_order' => 'nullable|integer',
            'department_id' => 'nullable|integer|exists:pgsql.sys_departments,id',
            'connection_type' => 'nullable|string|in:pgsql,company',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create([
            'key' => $data['key'],
            'label' => $data['label'],
            'route' => $data['route'] ?? null,
            'icon' => $data['icon'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'department_id' => $data['department_id'] ?? null,
            'connection_type' => $data['connection_type'] ?? 'pgsql',
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกเมนูสำเร็จ',
            'menu' => $menu
        ], 201);
    }

    public function updateApi(Request $request, $id)
    {
        $this->ensureAdmin();

        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string|max:100|unique:pgsql.sys_menus,key,' . $id,
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:pgsql.menus,id',
            'sort_order' => 'nullable|integer',
            'menu_group_id' => 'nullable|integer|exists:pgsql.menu_groups,id',
            'is_active' => 'boolean',
        ]);

        $menu->update($data);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตเมนูสำเร็จ',
            'menu' => $menu
        ]);
    }

    public function destroyApi($id)
    {
        $this->ensureAdmin();

        $menu = Menu::findOrFail($id);

        if ($menu->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบเมนูระบบได้'
            ], 403);
        }

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบเมนูสำเร็จ'
        ]);
    }

    public function toggleApi($id)
    {
        $this->ensureAdmin();

        $menu = Menu::findOrFail($id);
        $menu->is_active = !$menu->is_active;
        $menu->save();

        $status = $menu->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';

        return response()->json([
            'success' => true,
            'message' => "เปลี่ยนสถานะเป็น {$status} แล้ว",
            'menu' => $menu
        ]);
    }

    public function menus2()
    {
        $this->ensureAdmin();

        // Use Model instead of Query Builder
        $menus = Menu::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.menus-ajax', [
            'menus' => $menus,
        ]);
    }
}
