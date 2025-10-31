<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'roles';

    protected $fillable = [
        'name', 'description'
    ];

    /**
     * Get all roles
     */
    public static function getAllRoles()
    {
        return static::orderBy('name')->get();
    }

    /**
     * Get role by name
     */
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * Users belonging to this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Menu permissions for this role
     */
    public function menuPermissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class, 'role_id');
    }

    /**
     * Get permissions for specific menu
     */
    public function getMenuPermission(int $menuId)
    {
        return $this->menuPermissions()
            ->where('menu_id', $menuId)
            ->first();
    }
}
