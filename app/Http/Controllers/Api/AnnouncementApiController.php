<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Support\Str;

class AnnouncementApiController extends Controller
{
    /**
     * =====================================================
     * 🔹 LIST PENGUMUMAN AKTIF
     * Digunakan untuk:
     * - Dashboard slider (mobile)
     * - Halaman list pengumuman
     * =====================================================
     */
    public function index()
    {
        $announcements = Announcement::where('is_active', true)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get()
            ->map(function (Announcement $item) {
                return [
                    'id'            => $item->id,
                    'judul'         => $item->title,
                    'excerpt'       => Str::limit(strip_tags($item->content), 100),
                    'image_url'     => $item->image_url, // ✅ URL dari accessor model
                    'published_at'  => $item->published_at?->toISOString(),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $announcements,
        ]);
    }

    /**
     * =====================================================
     * 🔹 DETAIL PENGUMUMAN
     * Mobile: /announcements/{id}
     * =====================================================
     */
    public function show($id)
    {
        $announcement = Announcement::where('is_active', true)
            ->whereNotNull('published_at')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'            => $announcement->id,
                'judul'         => $announcement->title,
                'content'       => $announcement->content,
                'image_url'     => $announcement->image_url, // ✅ URL benar
                'published_at'  => $announcement->published_at?->toISOString(),
            ],
        ]);
    }
}
