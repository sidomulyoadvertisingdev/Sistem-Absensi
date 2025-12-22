@extends('layouts.app')

@section('title','Input Lembur')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <h1 class="mb-4">Input Lembur Manual</h1>

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <form action="{{ route('admin.lembur.store') }}" method="POST">
        @csrf

        {{-- KARYAWAN --}}
        <div class="form-group">
            <label>Karyawan</label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ old('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TANGGAL --}}
        <div class="form-group">
            <label>Tanggal Lembur</label>
            <input type="date"
                   name="tanggal"
                   class="form-control"
                   value="{{ old('tanggal') }}"
                   required>
        </div>

        {{-- JAM MULAI --}}
        <div class="form-group">
            <label>Jam Mulai</label>
            <input type="time"
                   name="jam_mulai"
                   class="form-control"
                   value="{{ old('jam_mulai') }}"
                   required>
        </div>

        {{-- JAM SELESAI --}}
        <div class="form-group">
            <label>Jam Selesai</label>
            <input type="time"
                   name="jam_selesai"
                   class="form-control"
                   value="{{ old('jam_selesai') }}"
                   required>
        </div>

        {{-- KETERANGAN --}}
        <div class="form-group">
            <label>Keterangan (Opsional)</label>
            <textarea name="keterangan"
                      class="form-control"
                      rows="3">{{ old('keterangan') }}</textarea>
        </div>

        {{-- BUTTON --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                Simpan Lembur
            </button>

            <a href="{{ route('admin.lembur') }}" class="btn btn-secondary">
                Kembali
            </a>
        </div>

    </form>

</div>
@endsection
