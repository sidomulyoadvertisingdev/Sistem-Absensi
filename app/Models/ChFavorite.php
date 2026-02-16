<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Chatify\Traits\UUID;

class ChFavorite extends Model
{
    use UUID;

    protected $table = 'ch_favorites';

    protected $fillable = [
        'id',
        'user_id',
        'favorite_id',
    ];
}
