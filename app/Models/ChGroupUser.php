<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChGroupUser extends Model
{
    protected $table = 'ch_group_user';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
    ];
}
