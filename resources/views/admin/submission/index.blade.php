@extends('layouts.app')

@section('title', 'Pengajuan Masuk')

@section('content')
<h4 class="mb-3">Pengajuan Masuk</h4>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $s)
                <tr>
                    <td>{{ $s->created_at->format('d/m/Y') }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->nama }}</td>
                    <td>
                        <span class="badge 
                            {{ $s->status === 'pending' ? 'bg-warning' : '' }}
                            {{ $s->status === 'approved' ? 'bg-success' : '' }}
                            {{ $s->status === 'rejected' ? 'bg-danger' : '' }}">
                            {{ strtoupper($s->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.submission.show', $s) }}"
                           class="btn btn-sm btn-primary">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Belum ada pengajuan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
