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
@endsection
