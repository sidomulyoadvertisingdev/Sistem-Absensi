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
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->nama }}</td>
                        <td>{{ $row->keterangan ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.pelanggaran.master.jabatan.edit', $row) }}"
                               class="btn btn-warning btn-sm"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.pelanggaran.master.jabatan.destroy', $row) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus jabatan \"{{ $row->nama }}\"?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">
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
