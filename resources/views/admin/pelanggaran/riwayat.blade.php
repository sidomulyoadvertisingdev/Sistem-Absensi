@extends('layouts.app')

@section('title','Riwayat Pelanggaran')

@section('content')
<div class="container-fluid">

<h2>Riwayat Pelanggaran {{ $user->name }}</h2>

<table class="table table-bordered">
<tr>
    <th>Tanggal</th>
    <th>Pelanggaran</th>
    <th>Tindakan</th>
</tr>
@foreach($pelanggarans as $p)
<tr>
    <td>{{ $p->tanggal }}</td>
    <td>{{ $p->jenis->nama }}</td>
    <td>{{ $p->tindakan->nama ?? '-' }}</td>
</tr>
@endforeach
</table>

</div>
@endsection
