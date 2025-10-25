<?php

namespace App\Http\Controllers;

use App\Models\MenuGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuGroupsController extends Controller
{
    public function index()
    {
        $groups = MenuGroup::orderBy('sort_order')->get();
        return view('tailadmin.pages.admin.menu-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('tailadmin.pages.admin.menu-groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:menu_groups,key',
            'label' => 'required|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        MenuGroup::create($request->all());

        return redirect()->route('admin.menu-groups.index')->with('success', 'กลุ่มเมนูถูกสร้างแล้ว');
    }

    public function show($id)
    {
        $group = MenuGroup::findOrFail($id);
        // ดึงผู้ใช้ในกลุ่มนี้ (ผ่าน roles ที่มีเมนูในกลุ่มนี้)
        $users = DB::table('users as u')
            ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
            ->join('role_menu_permissions as rmp', 'rmp.role_id', '=', 'ur.role_id')
            ->join('menus as m', 'm.id', '=', 'rmp.menu_id')
            ->where('m.menu_group_id', $id)
            ->select('u.id', 'u.name', 'u.email')
            ->distinct()
            ->get();

        return view('tailadmin.pages.admin.menu-groups.show', compact('group', 'users'));
    }

    public function edit($id)
    {
        $group = MenuGroup::findOrFail($id);
        return view('tailadmin.pages.admin.menu-groups.edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = MenuGroup::findOrFail($id);

        $request->validate([
            'key' => 'required|string|unique:menu_groups,key,' . $id,
            'label' => 'required|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $group->update($request->all());

        return redirect()->route('admin.menu-groups.index')->with('success', 'กลุ่มเมนูถูกอัปเดตแล้ว');
    }

    public function destroy($id)
    {
        $group = MenuGroup::findOrFail($id);

        if ($group->is_default) {
            return redirect()->route('admin.menu-groups.index')->with('error', 'ไม่สามารถลบกลุ่ม default ได้');
        }

        $group->delete();

        return redirect()->route('admin.menu-groups.index')->with('success', 'กลุ่มเมนูถูกลบแล้ว');
    }

    // API method for AJAX
    public function list()
    {
        $groups = MenuGroup::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'menuGroups' => $groups
        ]);
    }
}
