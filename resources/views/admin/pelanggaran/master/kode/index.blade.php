@extends('layouts.app')

@section('title','Master Kode Pelanggaran')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Master Kode Pelanggaran</h1>
        <a href="{{ route('admin.pelanggaran.master.kode.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kode
        </a>
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
                        <th width="120">Kode</th>
                        <th>Nama Pelanggaran</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>
                            <span class="badge badge-dark">
                                {{ $row->kode }}
                            </span>
                        </td>
                        <td>{{ $row->nama }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ $row->kategori }}
                            </span>
                        </td>
                        <td>{{ $row->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Belum ada master kode pelanggaran
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
