<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('sort_order')->get();
        return view('admin.departments', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:sys_departments,key',
            'label' => 'required|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        Department::create($request->all());

        return redirect()->route('admin.departments.index')->with('success', 'แผนกถูกสร้างแล้ว');
    }

    public function show($id)
    {
        $department = Department::findOrFail($id);

        // ดึงผู้ใช้ในแผนกนี้
        $users = DB::connection('pgsql')
            ->table('sys_users')
            ->where('department_id', $id)
            ->select('id', 'name', 'email', 'is_active')
            ->get();

        return view('admin.departments.show', compact('department', 'users'));
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'key' => 'required|string|unique:sys_departments,key,' . $id,
            'label' => 'required|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $department->update($request->all());

        return redirect()->route('admin.departments.index')->with('success', 'แผนกถูกอัปเดตแล้ว');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->is_default) {
            return redirect()->route('admin.departments.index')->with('error', 'ไม่สามารถลบแผนก default ได้');
        }

        // ตรวจสอบว่ามีผู้ใช้ในแผนกนี้หรือไม่
        $userCount = DB::connection('pgsql')
            ->table('sys_users')
            ->where('department_id', $id)
            ->count();

        if ($userCount > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', "ไม่สามารถลบแผนกได้ เนื่องจากมีผู้ใช้ {$userCount} คนอยู่ในแผนกนี้");
        }

        $department->delete();

        return redirect()->route('admin.departments.index')->with('success', 'แผนกถูกลบแล้ว');
    }

    // API method for AJAX
    public function list()
    {
        $departments = Department::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'departments' => $departments
        ]);
    }
}
