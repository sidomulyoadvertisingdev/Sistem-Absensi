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
                        <th>Bukti Foto</th>
                        <th>Keterangan</th>
                        <th>Status Kerja</th>
                        <th>Status Gaji</th>
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
                            {{ $item->jam_selesai ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') : '-' }}
                        </td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center" style="gap:6px;">
                                @if($item->foto_mulai_url)
                                    <a href="{{ $item->foto_mulai_url }}" target="_blank" title="Foto Mulai Lembur">
                                        <img src="{{ $item->foto_mulai_url }}" alt="Foto Mulai" width="40" height="40" style="object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                                    </a>
                                @else
                                    <span class="text-muted" title="Belum ada foto mulai">-</span>
                                @endif

                                @if($item->foto_selesai_url)
                                    <a href="{{ $item->foto_selesai_url }}" target="_blank" title="Foto Selesai Lembur">
                                        <img src="{{ $item->foto_selesai_url }}" alt="Foto Selesai" width="40" height="40" style="object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                                    </a>
                                @else
                                    <span class="text-muted" title="Belum ada foto selesai">-</span>
                                @endif
                            </div>
                        </td>

                        <td>{{ $item->keterangan ?? '-' }}</td>

                        <td>
                            @if($item->status === 'finished')
                                <span class="badge badge-success">Selesai</span>
                            @elseif($item->status === 'requested')
                                <span class="badge badge-info">Berjalan</span>
                            @elseif($item->status === 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge badge-secondary">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($item->approved_at)
                                <span class="badge badge-success" title="Disetujui oleh {{ $item->approver?->name ?? '-' }} pada {{ $item->approved_at->format('d-m-Y H:i') }}">
                                    <i class="fas fa-check-circle"></i> Sudah Dihitung
                                </span>
                            @elseif($item->status === 'rejected')
                                <span class="badge badge-secondary">-</span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-hourglass-half"></i> Menunggu Approval
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if(!$item->approved_at && $item->status !== 'rejected')
                                <form action="{{ route('admin.lembur.approve', $item->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Setujui lembur ini untuk dihitung ke gaji?')">
                                    @csrf
                                    <button class="btn btn-sm btn-success" title="Setujui untuk gaji">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>

                                <form action="{{ route('admin.lembur.reject', $item->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tolak pengajuan lembur ini?')">
                                    @csrf
                                    <button class="btn btn-sm btn-warning" title="Tolak">
                                        <i class="fas fa-times"></i> Tolak
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
                        <td colspan="10" class="text-center text-muted">
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
