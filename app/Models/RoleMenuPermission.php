<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleMenuPermission extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'role_menu_permissions';

    protected $fillable = [
        'role_id', 'menu_id', 'can_view', 'can_create',
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
     * Role relationship
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Menu relationship
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get or create permission for role and menu
     */
    public static function getOrCreate(int $roleId, int $menuId)
    {
        $permission = static::where('role_id', $roleId)
            ->where('menu_id', $menuId)
            ->first();

        if (!$permission) {
            $permission = static::create([
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'can_view' => false,
                'can_create' => false,
                'can_update' => false,
                'can_delete' => false,
                'can_export' => false,
                'can_approve' => false,
            ]);
        }

        return $permission;
    }

    /**
     * Update permissions for a role and menu
     */
    public static function updatePermissions(int $roleId, int $menuId, array $permissions)
    {
        return static::updateOrCreate(
            ['role_id' => $roleId, 'menu_id' => $menuId],
            $permissions
        );
    }

    /**
     * Get all permissions for a role
     */
    public static function getPermissionsForRole(int $roleId)
    {
        return static::where('role_id', $roleId)
            ->with('menu')
            ->get();
    }
}
