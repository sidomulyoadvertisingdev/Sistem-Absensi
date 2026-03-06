@extends('layouts.app')

@section('title', 'API Integration Tokens')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-1">API Integration Tokens</h1>
            <p class="text-muted mb-0">Generate token Sanctum untuk aplikasi eksternal yang ingin terhubung ke endpoint integrasi.</p>
        </div>
        <a href="{{ route('admin.integration-tokens.docs') }}" class="btn btn-light">
            <i class="fas fa-book mr-1"></i> Lihat Dokumentasi API
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($generatedToken)
        <div class="alert alert-warning">
            <div class="font-weight-bold mb-2">Token baru hanya ditampilkan sekali.</div>
            <label class="mb-1 d-block">Simpan token ini di aplikasi tujuan:</label>
            <textarea class="form-control" rows="3" readonly>{{ $generatedToken['plain_text_token'] }}</textarea>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Generate Token</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.integration-tokens.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name">Nama Token</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Mobile Payroll SMPO"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="d-block">Hak Akses</label>
                            @foreach($abilityOptions as $ability => $label)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="ability_{{ md5($ability) }}"
                                        name="abilities[]"
                                        value="{{ $ability }}"
                                        {{ in_array($ability, old('abilities', ['integration.attendance.report.read']), true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="ability_{{ md5($ability) }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                            <small class="text-muted d-block mt-2">Izin write otomatis mencakup izin read payroll.</small>
                        </div>

                        <div class="form-group">
                            <label for="expires_in_days">Masa Berlaku</label>
                            <select name="expires_in_days" id="expires_in_days" class="form-control">
                                @foreach($expiryOptions as $days => $label)
                                    <option value="{{ $days }}" {{ (string) old('expires_in_days', '90') === (string) $days ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-1"></i> Generate Token
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Cara Pakai</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2">Gunakan token ini pada header berikut:</p>
                    <pre class="p-3 rounded border bg-light mb-3"><code>Authorization: Bearer {TOKEN}
Accept: application/json</code></pre>

                    <p class="mb-2">Contoh endpoint integrasi:</p>
                    <ul class="mb-3 pl-3">
                        <li><code>GET {{ $baseUrl }}/api/integrations/attendance/report</code></li>
                        <li><code>GET {{ $baseUrl }}/api/integrations/attendance/salaries</code></li>
                        <li><code>PUT {{ $baseUrl }}/api/integrations/attendance/salaries/{user}</code></li>
                    </ul>

                    <p class="mb-0 text-muted">Token login biasa tetap bisa dipakai admin, tetapi token integrasi ini lebih aman untuk dibagikan ke sistem lain karena hak aksesnya bisa dibatasi.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Daftar Token</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Pemilik</th>
                                    <th>Hak Akses</th>
                                    <th>Kedaluwarsa</th>
                                    <th>Terakhir Dipakai</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tokens as $token)
                                    @php
                                        $tokenOwner = $token->tokenable;
                                        $displayName = str_replace('integration:', '', (string) $token->name);
                                        $tokenAbilities = collect($token->abilities ?? [])
                                            ->map(fn ($ability) => $abilityOptions[$ability] ?? $ability)
                                            ->implode(', ');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $displayName }}</div>
                                            <small class="text-muted">Dibuat {{ optional($token->created_at)->diffForHumans() }}</small>
                                        </td>
                                        <td>{{ $tokenOwner?->name ?? '-' }}</td>
                                        <td>{{ $tokenAbilities !== '' ? $tokenAbilities : '-' }}</td>
                                        <td>
                                            @if($token->expires_at)
                                                {{ optional($token->expires_at)->format('d M Y H:i') }}
                                            @else
                                                <span class="badge badge-success">Tanpa batas</span>
                                            @endif
                                        </td>
                                        <td>{{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Belum pernah' }}</td>
                                        <td class="text-right">
                                            <form action="{{ route('admin.integration-tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Hapus token integrasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada token integrasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
