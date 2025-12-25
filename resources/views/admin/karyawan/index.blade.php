@extends('layouts.app')

@section('title','Data Karyawan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Data Karyawan</h1>
        <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Karyawan
        </a>
    </div>

    {{-- SEARCH --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.karyawan.index') }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari Nama atau NIK..."
                            value="{{ $search ?? '' }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

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
                            <span class="badge badge-info">
                                {{ $user->penempatan }}
                            </span>
                        </td>
                        <td>
                            {{ $user->created_at?->format('d-m-Y') ?? '-' }}
                        </td>
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
                                <button class="btn btn-danger btn-sm">
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
@endsection
