<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $table = 'cheques';
    protected $fillable = [
        'branch_code','bank','cheque_number','date','payee','amount','printed_at'
    ];
    protected $casts = [
        'date' => 'date',
        'printed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];
}

