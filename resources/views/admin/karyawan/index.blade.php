@extends('layouts.app')

@section('title','Data Karyawan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Data Karyawan</h1>

        <div class="btn-group">
            <a href="{{ route('admin.karyawan.export.csv') }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>

            {{-- 🔥 FIX: Bootstrap 4 --}}
            <button type="button"
                    class="btn btn-info"
                    data-toggle="modal"
                    data-target="#importModal">
                <i class="fas fa-file-upload"></i> Import CSV
            </button>

            <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah
            </a>
        </div>
    </div>

    {{-- ALERT --}}
    @foreach (['success','warning','error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }}">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Jabatan</th>
                        <th>Penempatan</th>
                        <th>Tgl Daftar</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->nik }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->jabatan }}</td>
                        <td>
                            <span class="badge badge-info">{{ $user->penempatan }}</span>
                        </td>
                        <td>{{ optional($user->created_at)->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('admin.karyawan.edit', $user->id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Data kosong
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= MODAL IMPORT ================= --}}
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">

        <form method="POST"
              action="{{ route('admin.karyawan.import.csv') }}"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Import Karyawan (CSV)</h5>
                {{-- 🔥 FIX --}}
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>File CSV</label>
                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".csv"
                           required>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Format Kolom CSV (WAJIB):</strong><br>
                    nama, nik, email, phone, address, jabatan, penempatan
                </div>
            </div>

            <div class="modal-footer">
                {{-- 🔥 FIX --}}
                <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                    Batal
                </button>

                <button type="submit"
                        class="btn btn-primary">
                    <i class="fas fa-upload"></i> Import
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
