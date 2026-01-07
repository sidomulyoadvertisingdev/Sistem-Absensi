@extends('layouts.app')

@section('title','Lowongan Pekerjaan')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Lowongan Pekerjaan</h1>
        <a href="{{ route('admin.jobs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Job
        </a>
    </div>

    {{-- SEARCH --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.jobs.index') }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Cari judul atau lokasi..."
                            value="{{ request('search') }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">
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
                        <th width="80">Poster</th>
                        <th>Judul</th>
                        <th>Lokasi</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($jobs as $job)
                    <tr>
                        {{-- THUMBNAIL --}}
                        <td class="text-center">
                            @if($job->thumbnail)
                                <img src="{{ asset('storage/'.$job->thumbnail) }}"
                                     class="img-thumbnail"
                                     style="max-height:60px;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- TITLE --}}
                        <td>
                            <strong>{{ $job->title }}</strong>
                        </td>

                        {{-- LOCATION --}}
                        <td>
                            {{ $job->location ?? '-' }}
                        </td>

                        {{-- TYPE --}}
                        <td>
                            <span class="badge badge-info">
                                {{ $job->job_type ?? '-' }}
                            </span>
                        </td>

                        {{-- STATUS --}}
                        <td>
                            @if($job->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>

                        {{-- DATE --}}
                        <td>
                            {{ $job->created_at?->format('d-m-Y') ?? '-' }}
                        </td>

                        {{-- ACTION --}}
                        <td class="text-center">

                            {{-- EDIT --}}
                            <a href="{{ route('admin.jobs.edit', $job) }}"
                               class="btn btn-warning btn-sm"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            {{-- DELETE --}}
                            <form action="{{ route('admin.jobs.destroy', $job) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus lowongan ini?')">
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
                        <td colspan="7" class="text-center text-muted">
                            Data lowongan tidak ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
