@extends('layouts.app')

@section('title', 'Dokumentasi API Integrasi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-1">Dokumentasi API Integrasi</h1>
            <p class="text-muted mb-0">Panduan teknis untuk aplikasi eksternal yang ingin terhubung ke sistem absensi dan payroll.</p>
        </div>
        <a href="{{ route('admin.integration-tokens.index') }}" class="btn btn-light">
            <i class="fas fa-key mr-1"></i> Kelola Token
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Aturan Umum</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <h5>Base URL</h5>
                    <pre class="p-3 rounded border bg-light"><code>{{ $baseUrl }}</code></pre>

                    <h5>Header Wajib</h5>
                    <pre class="p-3 rounded border bg-light"><code>Authorization: Bearer {TOKEN}
Accept: application/json
Content-Type: application/json</code></pre>
                </div>
                <div class="col-lg-6">
                    <h5>Hak Akses Token</h5>
                    <ul class="pl-3 mb-0">
                        @foreach($abilityOptions as $ability => $label)
                            <li><code>{{ $ability }}</code> - {{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @foreach($endpointDocs as $endpoint)
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-1">{{ $endpoint['title'] }}</h3>
                    <small class="text-muted">{{ $endpoint['description'] }}</small>
                </div>
                <span class="badge badge-primary">{{ $endpoint['method'] }}</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="font-weight-bold mb-1">Endpoint</div>
                    <pre class="p-3 rounded border bg-light mb-0"><code>{{ $endpoint['url'] }}</code></pre>
                </div>

                <div class="mb-3">
                    <div class="font-weight-bold mb-1">Ability Token</div>
                    <code>{{ $endpoint['ability'] }}</code>
                </div>

                <div class="mb-3">
                    <div class="font-weight-bold mb-2">Parameter</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Wajib</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($endpoint['params'] as $param)
                                    <tr>
                                        <td><code>{{ $param['name'] }}</code></td>
                                        <td>{{ $param['type'] }}</td>
                                        <td>{{ $param['required'] ? 'Ya' : 'Tidak' }}</td>
                                        <td>{{ $param['description'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="font-weight-bold mb-1">Contoh Request</div>
                    <pre class="p-3 rounded border bg-dark text-white mb-0"><code>{{ $endpoint['curl'] }}</code></pre>
                </div>

                <div>
                    <div class="font-weight-bold mb-1">Contoh Response</div>
                    <pre class="p-3 rounded border bg-light mb-0"><code>{{ $endpoint['response'] }}</code></pre>
                </div>
            </div>
        </div>
    @endforeach

    <div class="card mb-4 border-primary">
        <div class="card-header">
            <h3 class="card-title mb-0">Webhook Keluar (Sistem → Aplikasi Eksternal)</h3>
        </div>
        <div class="card-body">
            <p>Sistem akan mengirim <strong>POST JSON</strong> ke URL webhook yang kamu daftarkan
                (menu <em>Webhook (Notifikasi)</em>) setiap kali ada user yang melakukan absen di aplikasi.</p>

            <div class="mb-3">
                <div class="font-weight-bold mb-1">Header</div>
                <pre class="p-3 rounded border bg-light mb-0"><code>Content-Type: application/json
X-Integration-Signature: sha256=&lt;HMAC_SHA256(body, secret)&gt;
User-Agent: SidoMulyo-Webhook</code></pre>
            </div>

            <div class="mb-3">
                <div class="font-weight-bold mb-1">Contoh Body</div>
                <pre class="p-3 rounded border bg-light mb-0"><code>{
  "event": "attendance.recorded",
  "occurred_at": "2026-07-16T08:05:00+00:00",
  "data": {
    "user_id": 7,
    "nik": "KRY0001",
    "name": "Budi Santoso",
    "jabatan": "Kasir",
    "penempatan": "Outlet A",
    "tanggal": "2026-03-01",
    "aksi": "masuk",
    "jam_masuk": "08:05:00",
    "istirahat_mulai": null,
    "istirahat_selesai": null,
    "jam_pulang": null,
    "status": "hadir",
    "menit_terlambat": 0
  }
}</code></pre>
            </div>

            <p class="mb-0">Verifikasi signature di sisi penerima dengan <code>hash_hmac('sha256', $rawBody, $secret)</code>
                dan bandingkan dengan header <code>X-Integration-Signature</code> (tanpa prefix <code>sha256=</code>).</p>
        </div>
    </div>
@endsection
