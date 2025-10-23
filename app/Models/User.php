<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    protected $connection = 'pgsql';
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Roles relationship
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * User-specific menu permissions
     */
    public function menuPermissions()
    {
        return $this->hasMany(UserMenuPermission::class, 'user_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user can perform action on menu
     */
    public function canAccessMenu(string $menuKey, string $action = 'view'): bool
    {
        $menu = Menu::findByKey($menuKey);
        if (!$menu) return false;

        // Check user-specific permission first
        $userPerm = $this->menuPermissions()
            ->where('menu_id', $menu->id)
            ->first();

        if ($userPerm) {
            $field = 'can_' . $action;
            return $userPerm->$field ?? false;
        }

        // Fall back to role permissions
        foreach ($this->roles as $role) {
            $rolePerm = $role->getMenuPermission($menu->id);
            if ($rolePerm) {
                $field = 'can_' . $action;
                if ($rolePerm->$field) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all accessible menus for this user
     */
    public function getAccessibleMenus()
    {
        $menus = Menu::getAllMenus();
        return $menus->filter(function ($menu) {
            return $this->canAccessMenu($menu->key, 'view');
        });
    }
}
