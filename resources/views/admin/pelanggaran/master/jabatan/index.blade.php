@extends('layouts.app')

@section('title','Master Jabatan')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Master Jabatan</h1>

        <a href="{{ route('admin.pelanggaran.master.jabatan.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Jabatan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama Jabatan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->nama }}</td>
                        <td>{{ $row->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted">
                            Belum ada data jabatan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
