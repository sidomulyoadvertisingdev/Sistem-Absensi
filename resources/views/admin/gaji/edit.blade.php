@extends('layouts.app')

@section('title', 'Atur Gaji Karyawan')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h4 class="mb-0 font-weight-bold">Atur Gaji Karyawan</h4>
        <small class="text-muted">
            Nama Karyawan: <strong>{{ $user->name }}</strong><br>
            Jabatan: {{ $user->jabatan ?? '-' }} |
            Penempatan: {{ $user->penempatan ?? '-' }}
        </small>
    </div>

    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFO SISTEM --}}
    <div class="alert alert-info">
        <strong>ℹ️ Informasi Sistem Gaji</strong>
        <ul class="mb-0 mt-2">
            <li>Gaji Pokok bersifat <strong>bulanan</strong></li>
            <li>Perhitungan harian = <strong>Gaji Pokok / 22 hari</strong></li>
            <li>Gaji dibayar sesuai <strong>hari hadir</strong></li>
            <li>Potongan mengikuti <strong>Aturan Potongan Gaji</strong> yang aktif</li>
            <li>Aturan potongan dapat berupa <strong>nominal</strong> atau <strong>persentase (%)</strong></li>
        </ul>
    </div>

    @php
        $salary = $user->salary;
    @endphp

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.gaji.update', $user->id) }}">
                @csrf

                <div class="row">

                    {{-- GAJI POKOK --}}
                    <div class="col-md-6 mb-3">
                        <label>Gaji Pokok (Bulanan)</label>
                        <input type="number"
                               name="gaji_pokok"
                               class="form-control"
                               value="{{ old('gaji_pokok', $salary->gaji_pokok ?? 0) }}"
                               min="0"
                               required>
                        <small class="text-muted">
                            Akan dibagi otomatis per hari (22 hari kerja)
                        </small>
                    </div>

                    {{-- TUNJANGAN UMUM --}}
                    <div class="col-md-6 mb-3">
                        <label>Tunjangan Umum</label>
                        <input type="number"
                               name="tunjangan_umum"
                               class="form-control"
                               value="{{ old('tunjangan_umum', $salary->tunjangan_umum ?? 0) }}"
                               min="0">
                    </div>

                    {{-- TUNJANGAN TRANSPORT --}}
                    <div class="col-md-6 mb-3">
                        <label>Tunjangan Transport</label>
                        <input type="number"
                               name="tunjangan_transport"
                               class="form-control"
                               value="{{ old('tunjangan_transport', $salary->tunjangan_transport ?? 0) }}"
                               min="0">
                    </div>

                    {{-- TUNJANGAN THR --}}
                    <div class="col-md-6 mb-3">
                        <label>Tunjangan Hari Raya (THR)</label>
                        <input type="number"
                               name="tunjangan_thr"
                               class="form-control"
                               value="{{ old('tunjangan_thr', $salary->tunjangan_thr ?? 0) }}"
                               min="0">
                    </div>

                    {{-- TUNJANGAN KESEHATAN --}}
                    <div class="col-md-6 mb-3">
                        <label>Tunjangan Kesehatan</label>
                        <input type="number"
                               name="tunjangan_kesehatan"
                               class="form-control"
                               value="{{ old('tunjangan_kesehatan', $salary->tunjangan_kesehatan ?? 0) }}"
                               min="0">
                    </div>

                    {{-- LEMBUR --}}
                    <div class="col-md-6 mb-3">
                        <label>Lembur per Jam</label>
                        <input type="number"
                               name="lembur_per_jam"
                               class="form-control"
                               value="{{ old('lembur_per_jam', $salary->lembur_per_jam ?? 0) }}"
                               min="0">
                        <small class="text-muted">
                            Dikalikan total jam lembur yang disetujui
                        </small>
                    </div>

                </div>

                {{-- STATUS AKTIF --}}
                <div class="form-check mb-4">
                    <input type="checkbox"
                           name="aktif"
                           id="aktif"
                           class="form-check-input"
                           value="1"
                           {{ old('aktif', $salary->aktif ?? true) ? 'checked' : '' }}>
                    <label for="aktif" class="form-check-label">
                        Aktifkan gaji karyawan ini
                    </label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gaji') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>

                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Gaji
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
