@extends('layouts.app')
@section('title','Input Pelanggaran')

@section('content')
<div class="container-fluid">

<h1>Input Pelanggaran</h1>

<form method="POST" action="{{ route('admin.pelanggaran.store') }}" enctype="multipart/form-data">
@csrf

<input type="date" name="tanggal" class="form-control mb-2" required>

<select name="user_id" class="form-control mb-2" required>
<option value="">Pilih Karyawan</option>
@foreach($users as $u)
<option value="{{ $u->id }}">{{ $u->name }} - {{ $u->nik }}</option>
@endforeach
</select>

<select name="kode_pelanggaran" class="form-control mb-2" required>
<option value="">Kode Pelanggaran</option>
@foreach($pelanggaran as $p)
<option value="{{ $p->kode }}">{{ $p->kode }} - {{ $p->nama }}</option>
@endforeach
</select>

<select name="lokasi" class="form-control mb-2" required>
<option value="">Lokasi</option>
@foreach($lokasi as $l)
<option value="{{ $l->nama }}">{{ $l->nama }}</option>
@endforeach
</select>

<textarea name="kronologi" class="form-control mb-2" placeholder="Kronologi"></textarea>

<input type="file" name="bukti" class="form-control mb-2">

<select name="tindakan" class="form-control mb-2">
<option value="">Tindakan</option>
<option>SP1</option>
<option>SP2</option>
<option>SP3</option>
<option>Teguran</option>
</select>

<textarea name="catatan" class="form-control mb-3" placeholder="Catatan HRD"></textarea>

<button class="btn btn-danger">Simpan</button>
<a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary">Kembali</a>

</form>

</div>
@endsection
