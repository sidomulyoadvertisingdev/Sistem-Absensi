@extends('layouts.app')

@section('title', 'Webhook Integrasi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-1">Webhook Integrasi (Outbound)</h1>
            <p class="text-muted mb-0">Sistem akan mengirim notifikasi ke aplikasi eksternal setiap kali ada user yang absen di aplikasi.</p>
        </div>
        <a href="{{ route('admin.integration-webhooks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Webhook
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($generatedWebhook)
        <div class="alert alert-warning">
            <div class="font-weight-bold mb-2">Webhook baru dibuat. Secret hanya ditampilkan sekali:</div>
            <label class="mb-1 d-block">Webhook URL</label>
            <input class="form-control mb-2" readonly value="{{ $generatedWebhook['webhook_url'] }}">
            <label class="mb-1 d-block">Secret (untuk verifikasi signature HMAC)</label>
            <textarea class="form-control" rows="2" readonly>{{ $generatedWebhook['secret'] }}</textarea>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Daftar Webhook</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>URL</th>
                        <th>Event</th>
                        <th>Status</th>
                        <th>Error Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $webhook)
                        <tr>
                            <td>{{ $webhook->name }}</td>
                            <td><code>{{ $webhook->webhook_url }}</code></td>
                            <td>
                                @foreach($webhook->events ?? [] as $ev)
                                    <span class="badge badge-info">{{ $eventOptions[$ev] ?? $ev }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($webhook->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if($webhook->last_error)
                                    <small class="text-danger">{{ \Illuminate\Support\Str::limit($webhook->last_error, 80) }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.integration-webhooks.destroy', $webhook) }}" method="POST" onsubmit="return confirm('Hapus webhook ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada webhook terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
