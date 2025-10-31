<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUserTracking;

class Menu extends Model
{
    use HasFactory, HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_menus';

    protected $fillable = [
        'key', 'label', 'route', 'url', 'icon', 'parent_id', 'sort_order', 'is_active', 'has_sticky_note', 'is_system', 'system_type', 'connection_type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_sticky_note' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all menus ordered by sort_order
     */
    public static function getAllMenus()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get menu tree structure
     */
    public static function getMenuTree()
    {
        $menus = static::getAllMenus();
        $tree = [];
        $lookup = [];

        foreach ($menus as $menu) {
            $lookup[$menu->id] = $menu;
            $lookup[$menu->id]->children = [];
        }

        foreach ($menus as $menu) {
            if ($menu->parent_id && isset($lookup[$menu->parent_id])) {
                $lookup[$menu->parent_id]->children[] = $menu;
            } else {
                $tree[] = $menu;
            }
        }

        return $tree;
    }

    /**
     * Parent menu relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Children menus relationship
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get menu by key
     */
    public static function findByKey(string $key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * System Type relationship (renamed from department)
     */
    public function systemType(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'system_type');
    }

    /**
     * Scope for System menus
     */
    public function scopeSystem($query)
    {
        return $query->where('system_type', 1);
    }

    /**
     * Scope for Bplus menus
     */
    public function scopeBplus($query)
    {
        return $query->where('system_type', 2);
    }

    /**
     * Scope for active menus
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for parent menus only
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get system type name
     */
    public function getSystemTypeNameAttribute(): string
    {
        return $this->system_type == 1 ? 'ระบบ' : 'Bplus';
    }
}

