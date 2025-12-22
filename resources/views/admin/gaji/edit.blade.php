@extends('layouts.app')

@section('title', 'Atur Gaji Karyawan')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h1 class="mb-0">Atur Gaji Karyawan</h1>
        <small class="text-muted">
            Nama Karyawan: <strong>{{ $user->name }}</strong>
        </small>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Validation Error --}}
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

            <form method="POST" action="{{ route('admin.gaji.update', $user->id) }}">
                @csrf

                <div class="row">

                    {{-- Gaji Pokok --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gaji Pokok</label>
                        <input type="number"
                               name="gaji_pokok"
                               class="form-control"
                               value="{{ $user->salary->gaji_pokok ?? 0 }}"
                               required>
                    </div>

                    {{-- Uang Makan --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Uang Makan</label>
                        <input type="number"
                               name="uang_makan"
                               class="form-control"
                               value="{{ $user->salary->uang_makan ?? 0 }}">
                    </div>

                    {{-- Transport --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Transport</label>
                        <input type="number"
                               name="transport"
                               class="form-control"
                               value="{{ $user->salary->transport ?? 0 }}">
                    </div>

                    {{-- Lembur Per Jam --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lembur per Jam</label>
                        <input type="number"
                               name="lembur_per_jam"
                               class="form-control"
                               value="{{ $user->salary->lembur_per_jam ?? 0 }}">
                    </div>

                </div>

                {{-- Status Aktif --}}
                <div class="form-check mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           name="aktif"
                           id="aktif"
                           {{ ($user->salary->aktif ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="aktif">
                        Aktifkan gaji karyawan ini
                    </label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.gaji') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Gaji
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
