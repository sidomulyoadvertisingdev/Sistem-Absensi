@extends('layouts.app')

@section('title','Data Karyawan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Data Karyawan</h1>

        <div>
            {{-- EXPORT CSV --}}
            <a href="{{ route('admin.karyawan.export.csv') }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>

            {{-- IMPORT --}}
            <button type="button"
                    class="btn btn-info"
                    data-bs-toggle="modal"
                    data-bs-target="#importModal">
                <i class="fas fa-file-upload"></i> Import CSV
            </button>

            {{-- TAMBAH --}}
            <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Karyawan
            </a>
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.karyawan.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari Nama atau NIK..."
                               value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.karyawan.index') }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ALERT --}}
    @foreach (['success','warning','error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }}">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Jabatan</th>
                        <th>Penempatan</th>
                        <th>Tanggal Daftar</th>
                        <th width="120" class="text-center">Aksi</th>
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
                            <span class="badge bg-info">
                                {{ $user->penempatan }}
                            </span>
                        </td>
                        <td>{{ optional($user->created_at)->format('d-m-Y') }}</td>
                        <td class="text-center">

                            {{-- EDIT --}}
                            <a href="{{ route('admin.karyawan.edit', $user->id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- DELETE --}}
                            <form action="{{ route('admin.karyawan.destroy', $user->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Data karyawan tidak ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ================= MODAL IMPORT CSV ================= --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">

        <form action="{{ route('admin.karyawan.import.csv') }}"
              method="POST"
              enctype="multipart/form-data"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Import Data Karyawan (CSV)</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>File CSV</label>
                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".csv"
                           required>

                    <small class="text-muted d-block mt-2">
                        Urutan kolom:
                        <br>
                        <strong>Nama, NIK, Email, No HP, Alamat, Jabatan, Penempatan</strong>
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
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
