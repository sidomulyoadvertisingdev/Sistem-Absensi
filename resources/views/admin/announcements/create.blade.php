@extends('layouts.app')

@section('title', 'Buat Pengumuman')

@section('content')

<div class="container-fluid">
<h4 class="mb-3">Buat Pengumuman</h4>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ route('admin.announcements.store') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="form-group">
                <label>Isi Pengumuman</label>
                <textarea name="content" rows="5" class="form-control" required>{{ old('content') }}</textarea>
            </div>

            <div class="form-group">
                <label>Gambar Geser</label>
                <input type="file" name="image" class="form-control">
            </div>

            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan
            </button>
        </form>
    </div>
</div>
</div>

@endsection
