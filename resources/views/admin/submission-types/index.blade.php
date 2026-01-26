@extends('layouts.app')

@section('title', 'Jenis Pengajuan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Jenis Pengajuan</h4>

    <a href="{{ route('admin.submission-types.create') }}"
       class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Tambah Jenis
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Butuh Alasan</th>
                    <th>Butuh Lampiran</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                <tr>
                    <td>{{ $type->kode }}</td>
                    <td>{{ $type->nama }}</td>
                    <td>
                        <span class="badge {{ $type->butuh_alasan ? 'bg-success' : 'bg-secondary' }}">
                            {{ $type->butuh_alasan ? 'Ya' : 'Tidak' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $type->butuh_lampiran ? 'bg-success' : 'bg-secondary' }}">
                            {{ $type->butuh_lampiran ? 'Ya' : 'Tidak' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $type->aktif ? 'bg-success' : 'bg-secondary' }}">
                            {{ $type->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.submission-types.edit', $type) }}"
                           class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>

                        <form action="{{ route('admin.submission-types.toggle', $type) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm btn-info"
                                    onclick="return confirm('Ubah status jenis pengajuan ini?')">
                                {{ $type->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Belum ada jenis pengajuan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
