<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChGroup extends Model
{
    protected $table = 'ch_groups';

    protected $fillable = [
        'name',
        'created_by',
    ];
}
