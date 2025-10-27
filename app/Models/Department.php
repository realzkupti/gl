<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserTracking;

class Department extends Model
{
    use HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_departments';

    protected $fillable = ['key', 'label', 'sort_order', 'is_active', 'is_default'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Menus in this department
     */
    public function menus()
    {
        return $this->hasMany(Menu::class, 'department_id');
    }

    /**
     * Users in this department
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Department-specific menu permissions
     */
    public function menuPermissions()
    {
        return $this->hasMany(DepartmentMenuPermission::class, 'department_id');
    }
}
