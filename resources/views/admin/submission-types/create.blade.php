@extends('layouts.app')

@section('content')
<div class="container-fluid">
<h4>Tambah Jenis Pengajuan</h4>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.submission-types.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Kode</label>
                <input type="text" name="kode" class="form-control" value="{{ old('kode') }}" required>
            </div>

            <div class="mb-3">
                <label>Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
            </div>

            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-check">
                <input type="checkbox" name="butuh_alasan" value="1" class="form-check-input" id="alasan"
                    {{ old('butuh_alasan') ? 'checked' : '' }}>
                <label for="alasan" class="form-check-label">Butuh Alasan</label>
            </div>

            <div class="form-check">
                <input type="checkbox" name="butuh_lampiran" value="1" class="form-check-input" id="lampiran"
                    {{ old('butuh_lampiran') ? 'checked' : '' }}>
                <label for="lampiran" class="form-check-label">Butuh Lampiran</label>
            </div>

            <div class="form-check">
                <input type="checkbox" name="is_izin_pulang_awal" value="1" class="form-check-input" id="izinPulangAwal"
                    {{ old('is_izin_pulang_awal') ? 'checked' : '' }}>
                <label for="izinPulangAwal" class="form-check-label">
                    Izin Pulang Sebelum Jam Kerja Selesai
                </label>
            </div>

            <button class="btn btn-primary mt-3">Simpan</button>
        </form>
    </div>
</div>
</div>
@endsection
