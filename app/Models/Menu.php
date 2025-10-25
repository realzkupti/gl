<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'menus';

    protected $fillable = [
        'key', 'label', 'route', 'url', 'icon', 'parent_id', 'sort_order', 'is_active', 'is_system', 'roles', 'menu_group', 'menu_group_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function menuGroup()
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }
}

