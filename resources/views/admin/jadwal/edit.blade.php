@extends('layouts.app')

@section('title', 'Atur Jadwal Kerja')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h1 class="mb-0">Atur Jadwal Kerja</h1>
        <small class="text-muted">
            User: <strong>{{ $user->name }}</strong>
        </small>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.jadwal.update', $user->id) }}">
                @csrf

                <div class="row">

                    {{-- Jam Masuk --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jam Masuk</label>
                        <input type="time"
                               name="jam_masuk"
                               class="form-control"
                               value="{{ $user->workSchedule->jam_masuk ?? '' }}"
                               required>
                    </div>

                    {{-- Jam Pulang --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jam Pulang</label>
                        <input type="time"
                               name="jam_pulang"
                               class="form-control"
                               value="{{ $user->workSchedule->jam_pulang ?? '' }}"
                               required>
                    </div>

                    {{-- Istirahat Mulai --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mulai Istirahat</label>
                        <input type="time"
                               name="istirahat_mulai"
                               class="form-control"
                               value="{{ $user->workSchedule->istirahat_mulai ?? '' }}"
                               required>
                    </div>

                    {{-- Istirahat Selesai --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Selesai Istirahat</label>
                        <input type="time"
                               name="istirahat_selesai"
                               class="form-control"
                               value="{{ $user->workSchedule->istirahat_selesai ?? '' }}"
                               required>
                    </div>

                </div>

                {{-- Status Aktif --}}
                <div class="form-check mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           name="aktif"
                           id="aktif"
                           {{ ($user->workSchedule->aktif ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">
                        Aktifkan jadwal kerja user ini
                    </label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.jadwal') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Jadwal
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
