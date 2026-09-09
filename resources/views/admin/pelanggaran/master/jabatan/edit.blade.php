@extends('layouts.app')

@section('title','Edit Jabatan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h1 class="mb-0">Edit Jabatan</h1>
        <small class="text-muted">
            Master Data → Jabatan
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
                  action="{{ route('admin.pelanggaran.master.jabatan.update', $jabatan) }}">
                @csrf
                @method('PUT')

                {{-- NAMA JABATAN --}}
                <div class="form-group">
                    <label>Nama Jabatan</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="{{ old('nama', $jabatan->nama) }}"
                        placeholder="Contoh: Supervisor Produksi"
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
                        placeholder="Keterangan tambahan jabatan"
                    >{{ old('keterangan', $jabatan->keterangan) }}</textarea>
                </div>

                {{-- BUTTON --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>

                    <a href="{{ route('admin.pelanggaran.master.jabatan.index') }}"
                       class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection