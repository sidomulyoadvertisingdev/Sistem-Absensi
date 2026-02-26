@extends('layouts.app')

@section('title', 'Edit Jenis Pengajuan')

@section('content')
<h4>Edit Jenis Pengajuan</h4>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.submission-types.update', $type) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Kode</label>
        <input type="text" name="kode" class="form-control" value="{{ old('kode', $type->kode) }}" required>
    </div>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" value="{{ old('nama', $type->nama) }}" required>
    </div>

    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control">{{ old('deskripsi', $type->deskripsi) }}</textarea>
    </div>

    <div class="form-check">
        <input type="checkbox" name="butuh_alasan" value="1" class="form-check-input" id="alasan"
            {{ old('butuh_alasan', $type->butuh_alasan) ? 'checked' : '' }}>
        <label for="alasan" class="form-check-label">Butuh Alasan</label>
    </div>

    <div class="form-check">
        <input type="checkbox" name="butuh_lampiran" value="1" class="form-check-input" id="lampiran"
            {{ old('butuh_lampiran', $type->butuh_lampiran) ? 'checked' : '' }}>
        <label for="lampiran" class="form-check-label">Butuh Lampiran</label>
    </div>

    <div class="form-check">
        <input type="checkbox" name="is_izin_pulang_awal" value="1" class="form-check-input" id="izinPulangAwal"
            {{ old('is_izin_pulang_awal', $type->is_izin_pulang_awal) ? 'checked' : '' }}>
        <label for="izinPulangAwal" class="form-check-label">
            Izin Pulang Sebelum Jam Kerja Selesai
        </label>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('admin.submission-types.index') }}" class="btn btn-light">Batal</a>
    </div>
</form>
@endsection
