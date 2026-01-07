<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplicant;
use Illuminate\Http\Request;

class ApplyJobController extends Controller
{
    /**
     * POST /api/jobs/{job}/apply
     * Middleware: auth:sanctum
     */
    public function store(Request $request, Job $job)
    {
        /*
        |--------------------------------------------------------------------------
        | AUTH USER (WAJIB)
        |--------------------------------------------------------------------------
        */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | CEK LOWONGAN
        |--------------------------------------------------------------------------
        */
        if (! $job->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan sudah tidak aktif',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | CEK SUDAH PERNAH APPLY
        |--------------------------------------------------------------------------
        */
        $alreadyApplied = JobApplicant::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melamar lowongan ini',
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI FORM (SESUAI FRONTEND)
        |--------------------------------------------------------------------------
        */
        $validated = $request->validate([
            'cv' => 'required|file|mimes:pdf|max:2048',
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPLOAD CV
        |--------------------------------------------------------------------------
        */
        $cvPath = $request->file('cv')
            ->store('job-applicants/cv', 'public');

        /*
        |--------------------------------------------------------------------------
        | SIMPAN DATA PELAMAR
        |--------------------------------------------------------------------------
        */
        $applicant = JobApplicant::create([
            'job_id'  => $job->id,
            'user_id' => $user->id,            // 🔑 WAJIB
            'name'    => $user->name,
            'email'   => $user->email,
            'phone'   => $user->phone,
            'answers' => [
                'cv' => $cvPath,
            ],
            'status'  => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil dikirim',
            'data' => [
                'id'     => $applicant->id,
                'status' => $applicant->status,
            ],
        ], 201);
    }
}
