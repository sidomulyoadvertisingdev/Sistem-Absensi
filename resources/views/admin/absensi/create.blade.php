@extends('layouts.app')

@section('title','Input Absensi Manual')

@section('content')
<div class="container-fluid">

    <h1 class="mb-4">Input Absensi Manual</h1>

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

    <form action="{{ route('admin.absensi.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- KARYAWAN --}}
        <div class="form-group">
            <label>Karyawan</label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TANGGAL --}}
        <div class="form-group">
            <label>Tanggal Absensi</label>
            <input
                type="date"
                name="tanggal"
                class="form-control"
                value="{{ old('tanggal', date('Y-m-d')) }}"
                required
            >
            <small class="text-muted">
                Satu tanggal hanya memiliki satu data absensi
            </small>
        </div>

        {{-- AKSI ABSENSI --}}
        <div class="form-group">
            <label>Aksi Absensi</label>
            <select name="aksi" class="form-control" required>
                <option value="">-- Pilih Aksi --</option>
                <option value="masuk" {{ old('aksi') == 'masuk' ? 'selected' : '' }}>
                    Absen Masuk
                </option>
                <option value="istirahat_mulai" {{ old('aksi') == 'istirahat_mulai' ? 'selected' : '' }}>
                    Mulai Istirahat
                </option>
                <option value="istirahat_selesai" {{ old('aksi') == 'istirahat_selesai' ? 'selected' : '' }}>
                    Selesai Istirahat
                </option>
                <option value="pulang" {{ old('aksi') == 'pulang' ? 'selected' : '' }}>
                    Absen Pulang
                </option>
            </select>
        </div>

        {{-- JAM --}}
        <div class="form-group">
            <label>Jam</label>
            <input
                type="time"
                name="jam"
                class="form-control"
                value="{{ old('jam') }}"
                required
            >
            <small class="text-muted">
                Jam akan disimpan ke kolom sesuai aksi yang dipilih
            </small>
        </div>

        {{-- FOTO --}}
        <div class="form-group">
            <label>Foto (Opsional)</label>
            <input type="file" name="foto" class="form-control-file">
            <small class="text-muted">
                Kosongkan jika input manual tanpa foto
            </small>
        </div>

        {{-- BUTTON --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                Simpan Absensi
            </button>

            {{-- ✅ FIX DI SINI --}}
            <a href="{{ route('admin.absensi') }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>

    </form>

</div>
@endsection
