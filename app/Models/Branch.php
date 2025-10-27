<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserTracking;

class Branch extends Model
{
    use HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_branches';
    protected $fillable = ['code', 'name'];
}
