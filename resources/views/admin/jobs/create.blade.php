@extends('layouts.app')

@section('title','Tambah Lowongan Pekerjaan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Tambah Lowongan Pekerjaan</h1>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- ALERT VALIDATION --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <div class="card">
        <div class="card-body">

            <form id="job-form"
                  method="POST"
                  action="{{ route('admin.jobs.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="row">

                    {{-- Judul --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Judul Lowongan</label>
                        <input type="text"
                               name="title"
                               class="form-control"
                               required
                               value="{{ old('title') }}">
                    </div>

                    {{-- Tipe --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Pekerjaan</label>
                        <input type="text"
                               name="job_type"
                               class="form-control"
                               value="{{ old('job_type') }}">
                    </div>

                    {{-- Thumbnail --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Poster / Thumbnail</label>
                        <input type="file"
                               name="thumbnail"
                               class="form-control"
                               accept="image/*"
                               onchange="previewThumbnail(event)">
                    </div>

                    {{-- Preview --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preview Poster</label>
                        <img id="thumbnail-preview"
                             src="{{ asset('images/no-image.png') }}"
                             class="img-thumbnail"
                             style="max-height:150px;">
                    </div>

                    {{-- Lokasi --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text"
                               name="location"
                               class="form-control"
                               value="{{ old('location') }}">
                    </div>

                    {{-- Deadline --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Batas Pendaftaran</label>
                        <input type="date"
                               name="deadline"
                               class="form-control"
                               value="{{ old('deadline') }}">
                    </div>

                    {{-- DESKRIPSI --}}
                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi Pekerjaan</label>

                        {{-- ⚠️ JANGAN PAKAI required --}}
                        <textarea
                            id="job-description"
                            name="description"
                            class="form-control"
                            rows="6"
                        >{{ old('description') }}</textarea>

                        <small class="text-muted">
                            Gunakan editor untuk membuat deskripsi rapi
                        </small>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                </div>

                {{-- ACTION --}}
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-save"></i> Simpan
                </button>

            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

<script>
let editor;

ClassicEditor
    .create(document.querySelector('#job-description'))
    .then(e => {
        editor = e;
    })
    .catch(error => console.error(error));

// 🔑 SYNC KE TEXTAREA SAAT SUBMIT
document.getElementById('job-form').addEventListener('submit', function () {
    document.querySelector('#job-description').value = editor.getData();
});

// Preview thumbnail
function previewThumbnail(event) {
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('thumbnail-preview').src = e.target.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endpush
