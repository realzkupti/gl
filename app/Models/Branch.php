<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'branches';
    protected $fillable = ['code', 'name'];
}
