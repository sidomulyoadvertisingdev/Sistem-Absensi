@extends('layouts.app')

@section('content')
<h4>Tambah Jenis Pengajuan</h4>

<form action="{{ route('admin.submission-types.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label>Kode</label>
        <input type="text" name="kode" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control"></textarea>
    </div>

    <div class="form-check">
        <input type="checkbox" name="butuh_alasan" class="form-check-input" id="alasan">
        <label for="alasan" class="form-check-label">Butuh Alasan</label>
    </div>

    <div class="form-check">
        <input type="checkbox" name="butuh_lampiran" class="form-check-input" id="lampiran">
        <label for="lampiran" class="form-check-label">Butuh Lampiran</label>
    </div>

    <button class="btn btn-primary mt-3">Simpan</button>
</form>
@endsection
