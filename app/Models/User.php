<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Traits\HasUserTracking;

class User extends Authenticatable
{
    protected $connection = 'pgsql';
    protected $table = 'sys_users';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasUserTracking;

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
        'department_id',
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
     * Department relationship
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * User-specific menu permissions
     */
    public function menuPermissions()
    {
        return $this->hasMany(UserMenuPermission::class, 'user_id');
    }

    /**
     * Companies this user has access to
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'sys_user_company_access', 'user_id', 'company_id')
            ->withTimestamps();
    }

    /**
     * Check if user has access to company
     */
    public function hasCompanyAccess(int $companyId): bool
    {
        return $this->companies()->where('company_id', $companyId)->exists();
    }

    /**
     * Get all accessible menus for this user
     * Combines department permissions + user-specific permissions
     */
    public function getAccessibleMenus()
    {
        // ถ้าไม่มีแผนก → ไม่มีสิทธิ์
        if (!$this->department_id) {
            return collect([]);
        }

        // สิทธิ์จากแผนก
        $departmentMenuIds = \App\Models\DepartmentMenuPermission::where('department_id', $this->department_id)
            ->pluck('menu_id');

        // สิทธิ์เพิ่มเติมของ user
        $userMenuIds = $this->menuPermissions()->pluck('menu_id');

        // รวมกัน
        $allMenuIds = $departmentMenuIds->merge($userMenuIds)->unique();

        return Menu::whereIn('id', $allMenuIds)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Check if user has access to specific menu by route name
     */
    public function hasMenuAccess($routeName): bool
    {
        $menu = Menu::where('route', $routeName)->first();
        if (!$menu) return true; // ถ้าไม่มีเมนูนี้ → อนุญาต

        $accessibleMenuIds = $this->getAccessibleMenus()->pluck('id');
        return $accessibleMenuIds->contains($menu->id);
    }

    /**
     * Get all accessible companies for this user
     */
    public function getAccessibleCompanies()
    {
        return $this->companies;
    }

    /**
     * Check if user has access to specific company
     */
    public function hasAccessToCompany($companyId): bool
    {
        return $this->companies()->where('companies.id', $companyId)->exists();
    }

    /**
     * Get current company from session
     */
    public function getCurrentCompany()
    {
        $companyId = session('current_company_id');
        if (!$companyId) {
            return null;
        }

        return Company::find($companyId);
    }

    /**
     * Set current company in session
     */
    public function setCurrentCompany($companyId): bool
    {
        if (!$this->hasAccessToCompany($companyId)) {
            return false;
        }

        session(['current_company_id' => $companyId]);
        return true;
    }
}
