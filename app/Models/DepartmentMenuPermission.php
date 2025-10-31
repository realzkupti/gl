<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUserTracking;

class DepartmentMenuPermission extends Model
{
    use HasFactory, HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_department_menu_permissions';

    protected $fillable = [
        'department_id', 'menu_id', 'can_view', 'can_create',
        'can_update', 'can_delete', 'can_export', 'can_approve'
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
        'can_export' => 'boolean',
        'can_approve' => 'boolean',
    ];

    /**
     * Department relationship
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Menu relationship
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get permissions for a department and menu
     */
    public static function getDepartmentMenuPermission(int $departmentId, int $menuId)
    {
        return static::where('department_id', $departmentId)
            ->where('menu_id', $menuId)
            ->first();
    }

    /**
     * Update or create department permission
     */
    public static function setPermission(int $departmentId, int $menuId, array $permissions)
    {
        return static::updateOrCreate(
            ['department_id' => $departmentId, 'menu_id' => $menuId],
            $permissions
        );
    }

    /**
     * Get all permissions for a department
     */
    public static function getPermissionsForDepartment(int $departmentId)
    {
        return static::where('department_id', $departmentId)
            ->with('menu')
            ->get();
    }

    /**
     * Remove department permission
     */
    public static function removePermission(int $departmentId, int $menuId)
    {
        return static::where('department_id', $departmentId)
            ->where('menu_id', $menuId)
            ->delete();
    }
}
