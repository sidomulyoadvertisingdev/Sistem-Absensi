<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * 🔥 Kirim otomatis ke frontend
     */
    protected $appends = ['image_url'];

    /**
     * 🔗 URL gambar pengumuman
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        // ✅ image sudah: announcements/xxx.jpg
        return asset('storage/' . $this->image);
    }
}
