@extends('layouts.app')

@section('title','Master Lokasi')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Master Lokasi</h1>
        <a href="{{ route('admin.pelanggaran.master.lokasi.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Lokasi
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
                        <th width="5%">#</th>
                        <th>Nama Lokasi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->nama }}</td>
                        <td>{{ $row->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            Belum ada data lokasi
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
