@extends('layouts.app')

@section('title', 'Data Lembur')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Data Lembur</h1>

        <a href="{{ route('admin.lembur.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Input Lembur
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
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $item->user?->name ?? '-' }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                        </td>

                        <td>{{ $item->keterangan ?? '-' }}</td>

                        <td>
                            @if($item->status === 'approved')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($item->status === 'pending')
                                <span class="badge badge-warning">Menunggu</span>
                            @else
                                <span class="badge badge-secondary">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($item->status !== 'approved')
                                <form action="{{ route('admin.lembur.approve', $item->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Setujui lembur ini?')">
                                    @csrf
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.lembur.destroy', $item->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Hapus lembur ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Belum ada data lembur
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $data->links() }}
    </div>

</div>
@endsection
