@extends('layouts.app')

@section('title','Tambah Lokasi Absensi')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h1 class="mb-0">Tambah Lokasi Absensi</h1>
        <small class="text-muted">Kehadiran → Lokasi Absensi</small>
    </div>

    <div class="card">
        <div class="card-body">
            @include('admin.lokasi-absensi._form', [
                'action' => route('admin.lokasi-absensi.store'),
                'method' => 'POST',
            ])
        </div>
    </div>

</div>
@endsection
