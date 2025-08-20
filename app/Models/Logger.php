<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logger extends Model
{
    protected $fillable = [
        'who',
        'details',
    ];
}
