@extends('layouts.app')

@section('title','Tambah Karyawan')

@section('content')
<div class="container-fluid">

    <h1 class="mb-4">Tambah Karyawan</h1>

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

    <form method="POST" action="{{ route('admin.karyawan.store') }}">
        @csrf

        {{-- NAMA --}}
        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                required
            >
        </div>

        {{-- NIK --}}
        <div class="mb-3">
            <label>NIK KTP</label>
            <input
                type="text"
                name="nik"
                class="form-control"
                value="{{ old('nik') }}"
                required
            >
        </div>

        {{-- EMAIL --}}
        <div class="mb-3">
            <label>Email Aktif</label>
            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
            >
        </div>

        {{-- NO HP --}}
        <div class="mb-3">
            <label>No. Handphone</label>
            <input
                type="text"
                name="phone"
                class="form-control"
                value="{{ old('phone') }}"
                required
            >
        </div>

        {{-- ALAMAT --}}
        <div class="mb-3">
            <label>Alamat Lengkap</label>
            <textarea
                name="address"
                class="form-control"
                rows="3"
                required
            >{{ old('address') }}</textarea>
        </div>

        {{-- JABATAN --}}
        <div class="mb-3">
            <label>Jabatan</label>
            <input
                type="text"
                name="jabatan"
                class="form-control"
                value="{{ old('jabatan') }}"
                required
            >
        </div>

        {{-- PENEMPATAN --}}
        <div class="mb-3">
            <label>Penempatan Kerja</label>
            <select name="penempatan" class="form-control" required>
                <option value="">-- Pilih Penempatan --</option>

                <option value="SM Lecy"
                    {{ old('penempatan') == 'SM Lecy' ? 'selected' : '' }}>
                    SM Lecy
                </option>

                <option value="SM Percetakan"
                    {{ old('penempatan') == 'SM Percetakan' ? 'selected' : '' }}>
                    SM Percetakan
                </option>

                <option value="SM Gudang"
                    {{ old('penempatan') == 'SM Gudang' ? 'selected' : '' }}>
                    SM Gudang
                </option>
            </select>
        </div>

        {{-- PASSWORD --}}
        <div class="mb-4">
            <label>Password Akun</label>
            <input
                type="password"
                name="password"
                class="form-control"
                required
            >
        </div>

        {{-- BUTTON --}}
        <button type="submit" class="btn btn-primary">
            Simpan Karyawan
        </button>

        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">
            Kembali
        </a>

    </form>

</div>
@endsection
