@extends('layouts.app')

@section('title','Edit Kode Pelanggaran')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h1 class="mb-0">Edit Kode Pelanggaran</h1>
        <small class="text-muted">
            Master Data → Kode Pelanggaran
        </small>
    </div>

    {{-- ERROR VALIDATION --}}
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
            <form method="POST"
                  action="{{ route('admin.pelanggaran.master.kode.update', $kode) }}">
                @csrf
                @method('PUT')

                {{-- KODE --}}
                <div class="form-group">
                    <label>Kode Pelanggaran</label>
                    <input
                        type="text"
                        name="kode"
                        class="form-control"
                        value="{{ old('kode', $kode->kode) }}"
                        placeholder="Contoh: PLG-001"
                        required
                    >
                </div>

                {{-- NAMA PELANGGARAN --}}
                <div class="form-group">
                    <label>Nama Pelanggaran</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="{{ old('nama', $kode->nama) }}"
                        placeholder="Contoh: Tidak Masuk Kerja"
                        required
                    >
                </div>

                {{-- KATEGORI --}}
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Ringan" {{ old('kategori', $kode->kategori) == 'Ringan' ? 'selected' : '' }}>
                            Ringan
                        </option>
                        <option value="Sedang" {{ old('kategori', $kode->kategori) == 'Sedang' ? 'selected' : '' }}>
                            Sedang
                        </option>
                        <option value="Berat" {{ old('kategori', $kode->kategori) == 'Berat' ? 'selected' : '' }}>
                            Berat
                        </option>
                    </select>
                </div>

                {{-- KETERANGAN --}}
                <div class="form-group">
                    <label>Keterangan (Opsional)</label>
                    <textarea
                        name="keterangan"
                        class="form-control"
                        rows="3"
                        placeholder="Penjelasan tambahan pelanggaran"
                    >{{ old('keterangan', $kode->keterangan) }}</textarea>
                </div>

                {{-- BUTTON --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>

                    <a href="{{ route('admin.pelanggaran.master.kode.index') }}"
                       class="btn btn-secondary">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection