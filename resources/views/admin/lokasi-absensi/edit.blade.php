@extends('layouts.app')

@section('title','Edit Lokasi Absensi')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h1 class="mb-0">Edit Lokasi Absensi</h1>
        <small class="text-muted">Kehadiran → Lokasi Absensi → {{ $location->nama }}</small>
    </div>

    <div class="card">
        <div class="card-body">
            @include('admin.lokasi-absensi._form', [
                'action' => route('admin.lokasi-absensi.update', $location),
                'method' => 'PUT',
            ])
        </div>
    </div>

</div>
@endsection
