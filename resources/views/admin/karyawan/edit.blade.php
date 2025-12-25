@extends('layouts.app')

@section('title', 'Edit Karyawan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Edit Karyawan</h1>
        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- ALERT ERROR --}}
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
            <form action="{{ route('admin.karyawan.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- NAMA --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $user->name) }}"
                               required>
                    </div>

                    {{-- NIK --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text"
                               name="nik"
                               class="form-control"
                               value="{{ old('nik', $user->nik) }}"
                               required>
                    </div>

                    {{-- EMAIL --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>

                    {{-- NO HP --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No HP</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               value="{{ old('phone', $user->phone) }}"
                               required>
                    </div>

                    {{-- JABATAN --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text"
                               name="jabatan"
                               class="form-control"
                               value="{{ old('jabatan', $user->jabatan) }}"
                               required>
                    </div>

                    {{-- PENEMPATAN --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Penempatan</label>
                        <input type="text"
                               name="penempatan"
                               class="form-control"
                               value="{{ old('penempatan', $user->penempatan) }}"
                               required>
                    </div>

                    {{-- ALAMAT --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address"
                                  class="form-control"
                                  rows="3"
                                  required>{{ old('address', $user->address) }}</textarea>
                    </div>

                    {{-- PASSWORD --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small>
                        </label>
                        <input type="password"
                               name="password"
                               class="form-control">
                    </div>
                </div>

                {{-- BUTTON --}}
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
