<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'sys_activity_logs';
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'action', 'url', 'method', 'ip', 'user_agent', 'payload'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
