<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuGroup extends Model
{
    protected $fillable = ['key', 'label', 'sort_order', 'is_active', 'is_default'];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_group_id');
    }
}
