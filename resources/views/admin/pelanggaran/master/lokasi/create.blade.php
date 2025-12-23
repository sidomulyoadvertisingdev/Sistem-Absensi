@extends('layouts.app')

@section('title','Tambah Lokasi')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h1 class="mb-0">Tambah Lokasi</h1>
        <small class="text-muted">
            Master Data → Lokasi
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
                  action="{{ route('admin.pelanggaran.master.lokasi.store') }}">
                @csrf

                {{-- NAMA LOKASI --}}
                <div class="form-group">
                    <label>Nama Lokasi</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="{{ old('nama') }}"
                        placeholder="Contoh: SM Lecy / Gudang / SM Percetakan"
                        required
                    >
                </div>

                {{-- KETERANGAN --}}
                <div class="form-group">
                    <label>Keterangan (Opsional)</label>
                    <textarea
                        name="keterangan"
                        class="form-control"
                        rows="3"
                        placeholder="Keterangan tambahan lokasi"
                    >{{ old('keterangan') }}</textarea>
                </div>

                {{-- BUTTON --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>

                    <a href="{{ route('admin.pelanggaran.master.lokasi.index') }}"
                       class="btn btn-secondary">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
