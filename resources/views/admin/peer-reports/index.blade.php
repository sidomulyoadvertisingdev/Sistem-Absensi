@extends('layouts.app')
@section('title','Laporan Rekan')

@section('content')
<div class="container-fluid">

    <h1 class="mb-1">Laporan Rekan (Lapor Rekan)</h1>
    <p class="text-muted">
        Identitas <b>pelapor</b> hanya terlihat di halaman admin ini. Karyawan terlapor hanya melihat
        laporan yang <b>disetujui</b>, tanpa tahu siapa pelapornya.
    </p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="btn-group mb-3">
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $key => $label)
            <a href="{{ route('admin.peer-reports.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $label }}
                @if($key !== 'all')
                    <span class="badge badge-light">{{ $counts[$key] ?? 0 }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:120px">Tanggal</th>
                        <th style="width:160px">Pelapor</th>
                        <th style="width:180px">Terlapor</th>
                        <th style="width:120px">Kategori</th>
                        <th>Kronologi</th>
                        <th style="width:90px">Bukti</th>
                        <th style="width:100px">Status</th>
                        <th style="width:260px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td>{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $r->reporter->name ?? '—' }}
                                <div class="small text-muted">{{ $r->reporter->nik ?? '' }}</div>
                            </td>
                            <td>
                                <b>{{ $r->reportedUser->name ?? '—' }}</b>
                                <div class="small text-muted">{{ $r->reportedUser->jabatan ?? '' }}</div>
                            </td>
                            <td>{{ $r->kategori }}</td>
                            <td>
                                {{ $r->kronologi }}
                                @if($r->lokasi)
                                    <div class="small text-muted"><i class="fas fa-map-marker-alt"></i> {{ $r->lokasi }}</div>
                                @endif
                                @if($r->catatan_admin)
                                    <div class="small"><b>Catatan admin:</b> {{ $r->catatan_admin }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($r->bukti_path)
                                    <a href="{{ asset('storage/'.$r->bukti_path) }}" target="_blank">Lihat</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php $badge = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$r->status] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $badge }}">{{ ucfirst($r->status) }}</span>
                            </td>
                            <td>
                                @if($r->status === 'pending')
                                    <form action="{{ route('admin.peer-reports.approve', $r) }}" method="POST" class="mb-1">
                                        @csrf @method('PUT')
                                        <input type="text" name="catatan_admin" class="form-control form-control-sm mb-1" placeholder="Catatan (opsional)">
                                        <button class="btn btn-sm btn-success btn-block"
                                                onclick="return confirm('Setujui laporan ini? Akan tampil di profil karyawan terlapor.')">
                                            Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.peer-reports.reject', $r) }}" method="POST">
                                        @csrf @method('PUT')
                                        <button class="btn btn-sm btn-outline-danger btn-block"
                                                onclick="return confirm('Tolak laporan ini?')">
                                            Tolak
                                        </button>
                                    </form>
                                @else
                                    <div class="small text-muted mb-1">
                                        oleh {{ $r->reviewer->name ?? '—' }}<br>
                                        {{ $r->reviewed_at?->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                                <form action="{{ route('admin.peer-reports.destroy', $r) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0"
                                            onclick="return confirm('Hapus laporan ini permanen?')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $reports->links() }}</div>
</div>
@endsection
