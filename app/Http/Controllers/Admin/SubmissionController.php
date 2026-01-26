<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    /**
     * ===============================
     * LIST SEMUA PENGAJUAN
     * ===============================
     */
    public function index()
    {
        $submissions = Submission::with([
                'user:id,name',
                'type:id,nama'
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.submission.index', compact('submissions'));
    }

    /**
     * ===============================
     * DETAIL PENGAJUAN
     * ===============================
     */
    public function show(Submission $submission)
    {
        // load relasi dengan aman
        $submission->load([
            'user:id,name',
            'type:id,nama'
        ]);

        return view('admin.submission.show', compact('submission'));
    }

    /**
     * ===============================
     * APPROVE PENGAJUAN
     * ===============================
     */
    public function approve(Request $request, Submission $submission)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string',
        ]);

        $submission->update([
            'status' => 'approved',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return redirect()
            ->route('admin.submission.show', $submission)
            ->with('success', 'Pengajuan berhasil disetujui');
    }

    /**
     * ===============================
     * REJECT PENGAJUAN
     * ===============================
     */
    public function reject(Request $request, Submission $submission)
    {
        $request->validate([
            'catatan_admin' => 'required|string',
        ]);

        $submission->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return redirect()
            ->route('admin.submission.show', $submission)
            ->with('success', 'Pengajuan berhasil ditolak');
    }

    /**
     * ===============================
     * KEMBALIKAN KE PENDING (OPTIONAL)
     * ===============================
     */
    public function cancel(Submission $submission)
    {
        $submission->update([
            'status' => 'pending',
            'catatan_admin' => null,
        ]);

        return redirect()
            ->route('admin.submission.show', $submission)
            ->with('success', 'Status berhasil dikembalikan ke pending');
    }
}
