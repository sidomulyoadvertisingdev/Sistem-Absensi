<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * GET /api/jobs
     * List lowongan aktif
     */
    public function index(Request $request)
    {
        $jobs = Job::query()
            ->where('is_active', true)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('location', 'like', '%' . $request->search . '%');
                });
            })
            ->latest()
            ->get()
            ->map(function ($job) {
                return [
                    'id'        => $job->id,
                    'title'     => $job->title,
                    'location'  => $job->location,
                    'job_type'  => $job->job_type,
                    'deadline'  => $job->deadline,
                    // ✅ URL FULL (INI YANG BIKIN FOTO TAMPIL DI REACT)
                    'thumbnail' => $job->thumbnail
                        ? asset('storage/' . $job->thumbnail)
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $jobs,
        ]);
    }

    /**
     * GET /api/jobs/{job}
     * Detail lowongan + form persyaratan
     */
    public function show(Job $job)
    {
        if (!$job->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak tersedia',
            ], 404);
        }

        $job->load('formFields');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $job->id,
                'title'       => $job->title,
                'description' => $job->description,
                'location'    => $job->location,
                'job_type'    => $job->job_type,
                'deadline'    => $job->deadline,
                // ✅ URL FULL
                'thumbnail'   => $job->thumbnail
                    ? asset('storage/' . $job->thumbnail)
                    : null,
                'requirements' => $job->formFields->map(function ($field) {
                    return [
                        'id'       => $field->id,
                        'label'    => $field->label,
                        'name'     => $field->name,
                        'type'     => $field->type,
                        'required' => (bool) $field->required,
                    ];
                }),
            ],
        ]);
    }
}
