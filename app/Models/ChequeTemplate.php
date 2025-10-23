<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeTemplate extends Model
{
    protected $table = 'cheque_templates';
    protected $fillable = ['bank', 'template_json'];
    protected $casts = [
        'template_json' => 'array',
    ];
}

