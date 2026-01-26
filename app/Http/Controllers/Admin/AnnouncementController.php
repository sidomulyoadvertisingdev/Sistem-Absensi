<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * =========================
     * LIST PENGUMUMAN
     * =========================
     */
    public function index()
    {
        $announcements = Announcement::orderByDesc('published_at')->get();

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * =========================
     * FORM TAMBAH PENGUMUMAN
     * =========================
     */
    public function create()
    {
        return view('admin.announcements.create');
    }

    /**
     * =========================
     * SIMPAN PENGUMUMAN
     * =========================
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // 🔥 nama file aman & unik
            $imageName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // ⬇️ SIMPAN KE storage/app/public/announcements
            $file->storeAs('announcements', $imageName, 'public');
        }

        Announcement::create([
            'title'        => $request->title,
            'content'      => $request->content,
            'image'        => $imageName, // ✅ hanya nama file
            'is_active'    => true,
            'published_at' => now(),
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat');
    }

    /**
     * =========================
     * DETAIL PENGUMUMAN
     * =========================
     */
    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * =========================
     * AKTIF / NONAKTIF
     * =========================
     */
    public function toggle(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => ! $announcement->is_active,
        ]);

        return back()->with('success', 'Status pengumuman berhasil diubah');
    }
}
