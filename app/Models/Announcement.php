<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

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

        $image = $this->image;
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }
        if (Str::startsWith($image, 'storage/')) {
            return asset($image);
        }
        if (Str::startsWith($image, 'announcements/')) {
            return asset('storage/' . $image);
        }

        // ✅ file disimpan ke storage/app/public/announcements
        return asset('storage/announcements/' . $image);
    }
}
