@extends('layouts.app')

@section('title','Edit Lowongan Pekerjaan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Edit Lowongan Pekerjaan</h1>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= EDIT JOB ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Edit Data Lowongan</strong>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('admin.jobs.update', $job) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">

                    {{-- Judul --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Judul Lowongan</label>
                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            value="{{ old('title', $job->title) }}"
                            required
                        >
                    </div>

                    {{-- Tipe --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Pekerjaan</label>
                        <input
                            type="text"
                            name="job_type"
                            class="form-control"
                            value="{{ old('job_type', $job->job_type) }}"
                        >
                    </div>

                    {{-- Thumbnail --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Poster / Thumbnail</label>
                        <input
                            type="file"
                            name="thumbnail"
                            class="form-control"
                            accept="image/*"
                            onchange="previewThumbnail(event)"
                        >
                        <small class="text-muted">
                            JPG / PNG maksimal 2MB
                        </small>
                    </div>

                    {{-- Preview --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preview Poster</label>
                        <div>
                            @if($job->thumbnail)
                                <img
                                    id="thumbnail-preview"
                                    src="{{ asset('storage/'.$job->thumbnail) }}"
                                    class="img-thumbnail"
                                    style="max-height:150px;"
                                >
                            @else
                                <img
                                    id="thumbnail-preview"
                                    src="{{ asset('images/no-image.png') }}"
                                    class="img-thumbnail"
                                    style="max-height:150px;"
                                >
                            @endif
                        </div>
                    </div>

                    {{-- Lokasi --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi</label>
                        <input
                            type="text"
                            name="location"
                            class="form-control"
                            value="{{ old('location', $job->location) }}"
                        >
                    </div>

                    {{-- Deadline --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Batas Pendaftaran</label>
                        <input
                            type="date"
                            name="deadline"
                            class="form-control"
                            value="{{ old('deadline', $job->deadline) }}"
                        >
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi Pekerjaan</label>
                        <textarea
                            name="description"
                            rows="5"
                            class="form-control"
                            required
                        >{{ old('description', $job->description) }}</textarea>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status Lowongan</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ $job->is_active ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !$job->is_active ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                </div>

                {{-- ACTION --}}
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
            </form>
        </div>
    </div>

    {{-- ================= FORM PERSYARATAN ================= --}}
    <div class="card">
        <div class="card-header">
            <strong>Form Persyaratan Lamaran</strong>
        </div>

        <div class="card-body">

            {{-- TAMBAH FIELD --}}
            <form method="POST"
                  action="{{ route('admin.jobs.fields.store', $job) }}"
                  class="mb-4">
                @csrf

                <div class="row align-items-end">

                    <div class="col-md-4 mb-2">
                        <label>Label</label>
                        <input type="text" name="label" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Nama Field</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label>Tipe Input</label>
                        <select name="type" class="form-control" required>
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="file">File</option>
                            <option value="select">Select</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="required" class="form-check-input" id="required">
                            <label class="form-check-label" for="required">
                                Wajib
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-2">
                        <button class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Field
                        </button>
                    </div>

                </div>
            </form>

            {{-- LIST FIELD --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Label</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Wajib</th>
                            <th width="80" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($job->formFields as $field)
                        <tr>
                            <td>{{ $field->label }}</td>
                            <td><code>{{ $field->name }}</code></td>
                            <td>{{ ucfirst($field->type) }}</td>
                            <td>
                                @if($field->required)
                                    <span class="badge badge-success">Ya</span>
                                @else
                                    <span class="badge badge-secondary">Tidak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <form method="POST"
                                      action="{{ route('admin.jobs.fields.destroy', $field) }}"
                                      onsubmit="return confirm('Hapus field ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Belum ada field persyaratan
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function previewThumbnail(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('thumbnail-preview').src = e.target.result;
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
