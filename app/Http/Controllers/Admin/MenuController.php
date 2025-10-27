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
        if (!Auth::check() ) {
            abort(403, 'Forbidden');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        // Use Model instead of Query Builder
        $menus = Menu::with('systemType')->orderBy('sort_order')->orderBy('id')->get();

        // Create system types for tabs (not using departments table)
        $systemTypes = [
            ['id' => 1, 'key' => 'system', 'label' => 'System'],
            ['id' => 2, 'key' => 'bplus', 'label' => 'Bplus'],
        ];

        // Render the fully JS-driven view
        return view('admin.menus', [
            'menus' => $menus,
            'systemTypes' => $systemTypes,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->storeApi($request);
        }

        $data = $request->validate([
            'key' => 'required|string|max:100|unique:pgsql.sys_menus,key',
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:pgsql.sys_menus,id',
            'sort_order' => 'nullable|integer',
            'system_type' => 'nullable|integer|in:1,2',
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
            'system_type' => $data['system_type'] ?? 1,
            'connection_type' => $data['connection_type'] ?? 'pgsql',
            'is_active' => true,
        ]);

        return redirect()->route('admin.menus')->with('status', 'บันทึกเมนูแล้ว');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->updateApi($request, $id);
        }

        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string|max:100|unique:pgsql.sys_menus,key,' . $id,
            'label' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:pgsql.sys_menus,id',
            'sort_order' => 'nullable|integer',
            'system_type' => 'nullable|integer|in:1,2',
            'connection_type' => 'nullable|string|in:pgsql,company',
            'is_active' => 'boolean',
        ]);

        $menu->update($data);

        return redirect()->route('admin.menus')->with('status', 'อัปเดตเมนูแล้ว');
    }

    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin();

        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->destroyApi($id);
        }

        $menu = Menu::findOrFail($id);

        // Prevent deletion of system menus
        if ($menu->is_system) {
            return redirect()->route('admin.menus')
                ->with('error', 'ไม่สามารถลบเมนูระบบได้ (System Menu)');
        }

        $menu->delete();

        return redirect()->route('admin.menus')->with('status', 'ลบเมนูแล้ว');
    }

    public function toggle(Request $request, $id)
    {
        $this->ensureAdmin();

        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            return $this->toggleApi($id);
        }

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

        $menus = Menu::with('systemType')->orderBy('sort_order')->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'data' => $menus
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
            'system_type' => 'nullable|integer|in:1,2',
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
            'system_type' => $data['system_type'] ?? 1,
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
            'parent_id' => 'nullable|integer|exists:pgsql.sys_menus,id',
            'sort_order' => 'nullable|integer',
            'system_type' => 'nullable|integer|in:1,2',
            'connection_type' => 'nullable|string|in:pgsql,company',
            'is_active' => 'boolean',
        ]);

        $menu->update($data);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตเมนูสำเร็จ',
            'menu' => $menu
        ]);
    }

    public function reorder(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:pgsql.sys_menus,id',
            'order.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->order as $item) {
            Menu::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'อัพเดตลำดับสำเร็จ'
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
