<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUserTracking;

class UserMenuPermission extends Model
{
    use HasFactory, HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_user_menu_permissions';

    protected $fillable = [
        'user_id', 'menu_id', 'can_view', 'can_create',
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
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Menu relationship
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get permissions for a user and menu
     */
    public static function getUserMenuPermission(int $userId, int $menuId)
    {
        return static::where('user_id', $userId)
            ->where('menu_id', $menuId)
            ->first();
    }

    /**
     * Update or create user permission
     */
    public static function setPermission(int $userId, int $menuId, array $permissions)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'menu_id' => $menuId],
            $permissions
        );
    }

    /**
     * Get all permissions for a user
     */
    public static function getPermissionsForUser(int $userId)
    {
        return static::where('user_id', $userId)
            ->with('menu')
            ->get();
    }

    /**
     * Remove user permission
     */
    public static function removePermission(int $userId, int $menuId)
    {
        return static::where('user_id', $userId)
            ->where('menu_id', $menuId)
            ->delete();
    }
}
