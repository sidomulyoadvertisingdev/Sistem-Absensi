@extends('layouts.app')

@section('title', 'Atur Jadwal Kerja')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h1 class="mb-0">Atur Jadwal Kerja</h1>
        <small class="text-muted">
            Karyawan: <strong>{{ $user->name }}</strong>
        </small>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $hariList = [
            'senin'  => 'Senin',
            'selasa' => 'Selasa',
            'rabu'   => 'Rabu',
            'kamis'  => 'Kamis',
            'jumat'  => 'Jumat',
            'sabtu'  => 'Sabtu',
            'minggu' => 'Minggu',
        ];
    @endphp

    <form method="POST" action="{{ route('admin.jadwal.update', $user->id) }}">
        @csrf

        <div class="card">
            <div class="card-body">

                @foreach($hariList as $key => $label)
                    @php
                        $jadwal = $jadwal[$key] ?? null;
                    @endphp

                    <div class="border rounded p-3 mb-3">

                        {{-- CHECKBOX HARI --}}
                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="hari[{{ $key }}]"
                                   id="hari_{{ $key }}"
                                   {{ ($jadwal && $jadwal->aktif) ? 'checked' : '' }}>

                            <label class="form-check-label font-weight-bold"
                                   for="hari_{{ $key }}">
                                {{ $label }}
                            </label>

                            <small class="text-muted ml-2">
                                (tidak dicentang = libur)
                            </small>
                        </div>

                        {{-- JAM --}}
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label>Jam Masuk</label>
                                <input type="time"
                                       name="jam_masuk[{{ $key }}]"
                                       class="form-control"
                                       value="{{ $jadwal->jam_masuk ?? '' }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Jam Pulang</label>
                                <input type="time"
                                       name="jam_pulang[{{ $key }}]"
                                       class="form-control"
                                       value="{{ $jadwal->jam_pulang ?? '' }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Istirahat Mulai</label>
                                <input type="time"
                                       name="istirahat_mulai[{{ $key }}]"
                                       class="form-control"
                                       value="{{ $jadwal->istirahat_mulai ?? '' }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Istirahat Selesai</label>
                                <input type="time"
                                       name="istirahat_selesai[{{ $key }}]"
                                       class="form-control"
                                       value="{{ $jadwal->istirahat_selesai ?? '' }}">
                            </div>
                        </div>

                    </div>
                @endforeach

                {{-- BUTTON --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.jadwal') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Jadwal
                    </button>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection
