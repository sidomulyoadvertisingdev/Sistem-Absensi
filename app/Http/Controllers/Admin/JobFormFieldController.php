<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobFormField;
use Illuminate\Http\Request;

class JobFormFieldController extends Controller
{
    public function store(Request $request, Job $job)
    {
        $data = $request->validate([
            'label'    => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:text,textarea,file,select',
            'required' => 'nullable|boolean',
        ]);

        // Pastikan checkbox "required" terbaca benar
        $data['required'] = $request->has('required');

        $job->formFields()->create($data);

        return redirect()
            ->back()
            ->with('success', 'Field persyaratan berhasil ditambahkan');
    }

    public function destroy(JobFormField $field)
    {
        $field->delete();

        return redirect()
            ->back()
            ->with('success', 'Field persyaratan berhasil dihapus');
    }
}
