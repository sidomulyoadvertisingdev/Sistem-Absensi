<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplicant;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplicantController extends Controller
{
    /**
     * =====================================================
     * LIST SEMUA PELAMAR (SEMUA JOB)
     * Route: admin.jobs.applicants.all
     * =====================================================
     */
    public function indexAll(Request $request)
    {
        $query = JobApplicant::with('job');

        // 🔍 SEARCH (nama / email)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // 🔽 FILTER STATUS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applicants = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.jobs.applicants.index', [
            'applicants' => $applicants,
            'job'        => null, // penting agar blade aman
        ]);
    }

    /**
     * =====================================================
     * LIST PELAMAR PER JOB
     * Route: admin.jobs.applicants.index
     * =====================================================
     */
    public function index(Request $request, Job $job)
    {
        $query = $job->applicants()->with('job');

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // 🔽 FILTER STATUS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applicants = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.jobs.applicants.index', [
            'job'        => $job,
            'applicants' => $applicants,
        ]);
    }

    /**
     * =====================================================
     * UPDATE STATUS PELAMAR
     * =====================================================
     */
    public function updateStatus(Request $request, JobApplicant $applicant)
    {
        $request->validate([
            'status' => 'required|in:pending,review,interview,training,accepted,rejected',
        ]);

        $applicant->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status pelamar berhasil diperbarui');
    }

    /**
     * =====================================================
     * DOWNLOAD FILE / CV PELAMAR
     * Route: admin.jobs.applicants.download
     * =====================================================
     */
    public function downloadFile(JobApplicant $applicant, string $field)
    {
        $answers = $applicant->answers ?? [];

        if (!isset($answers[$field])) {
            abort(404, 'File tidak ditemukan');
        }

        $path = $answers[$field];

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak tersedia');
        }

        return Storage::disk('public')->download($path);
    }
}
