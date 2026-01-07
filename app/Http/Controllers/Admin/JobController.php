<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    /**
     * List lowongan
     */
    public function index(Request $request)
    {
        $jobs = Job::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->get();

        return view('admin.jobs.index', compact('jobs'));
    }

    /**
     * Form tambah lowongan
     */
    public function create()
    {
        return view('admin.jobs.create');
    }

    /**
     * Simpan lowongan baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string', // HTML dari CKEditor
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location'    => 'nullable|string|max:255',
            'job_type'    => 'nullable|string|max:255',
            'deadline'    => 'nullable|date',
            'is_active'   => 'required|boolean',
        ]);

        // Upload thumbnail jika ada
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')
                ->store('jobs/thumbnails', 'public');
        }

        Job::create($data);

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Lowongan pekerjaan berhasil ditambahkan');
    }

    /**
     * Form edit lowongan
     */
    public function edit(Job $job)
    {
        return view('admin.jobs.edit', compact('job'));
    }

    /**
     * Update lowongan
     */
    public function update(Request $request, Job $job)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string', // HTML CKEditor
            'thumbnail'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'location'    => 'nullable|string|max:255',
            'job_type'    => 'nullable|string|max:255',
            'deadline'    => 'nullable|date',
            'is_active'   => 'required|boolean',
        ]);

        // Jika upload thumbnail baru
        if ($request->hasFile('thumbnail')) {

            // hapus thumbnail lama
            if ($job->thumbnail && Storage::disk('public')->exists($job->thumbnail)) {
                Storage::disk('public')->delete($job->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')
                ->store('jobs/thumbnails', 'public');
        }

        $job->update($data);

        return redirect()
            ->route('admin.jobs.edit', $job)
            ->with('success', 'Lowongan pekerjaan berhasil diperbarui');
    }

    /**
     * Hapus lowongan
     */
    public function destroy(Job $job)
    {
        if ($job->thumbnail && Storage::disk('public')->exists($job->thumbnail)) {
            Storage::disk('public')->delete($job->thumbnail);
        }

        $job->delete();

        return redirect()
            ->route('admin.jobs.index')
            ->with('success', 'Lowongan pekerjaan berhasil dihapus');
    }
}
