@extends('layouts.app')
@section('title','Log Pelanggaran')

@section('content')
<div class="container-fluid">

<h1 class="mb-3">Log Pelanggaran</h1>

<a href="{{ route('admin.pelanggaran.create') }}" class="btn btn-danger mb-3">
    + Input Pelanggaran
</a>

<div class="card">
<div class="card-body table-responsive p-0">
<table class="table table-bordered table-hover">
<thead>
<tr>
<th>No</th>
<th>Tanggal</th>
<th>Nama</th>
<th>Jabatan</th>
<th>Lokasi</th>
<th>Kode</th>
<th>Jenis</th>
<th>Kategori</th>
<th>Tindakan</th>
<th>Penanggung Jawab</th>
</tr>
</thead>
<tbody>
@foreach($data as $i => $row)
<tr>
<td>{{ $i+1 }}</td>
<td>{{ $row->tanggal }}</td>
<td>{{ $row->user->name }}</td>
<td>{{ $row->jabatan }}</td>
<td>{{ $row->lokasi }}</td>
<td>{{ $row->kode_pelanggaran }}</td>
<td>{{ $row->jenis_pelanggaran }}</td>
<td>{{ $row->kategori }}</td>
<td>{{ $row->tindakan }}</td>
<td>{{ $row->penanggung_jawab }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>

</div>
@endsection
