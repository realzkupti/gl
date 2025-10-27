<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StickyNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'sys_sticky_notes';

    protected $fillable = [
        'user_id',
        'menu_id',
        'company_id',
        'content',
        'color',
        'position_x',
        'position_y',
        'width',
        'height',
        'is_minimized',
        'is_pinned',
        'z_index',
    ];

    protected $casts = [
        'is_minimized' => 'boolean',
        'is_pinned' => 'boolean',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'z_index' => 'integer',
    ];

    /**
     * Get the user that owns the sticky note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the menu associated with the sticky note.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the company associated with the sticky note (optional).
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
