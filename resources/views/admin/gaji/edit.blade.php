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

<div class="alert alert-info">
<strong>ℹ️ Informasi Sistem Gaji</strong>
<ul class="mb-0 mt-2">
<li>Gaji pokok → dasar aturan potongan</li>
<li>Gaji harian bisa otomatis dihitung</li>
<li>Tunjangan opsional masuk payroll</li>
<li>Lembur & bonus otomatis</li>
<li>Potongan mengikuti rule aktif</li>
</ul>
</div>

@php
$salary = $user->salary;
@endphp

<div class="card shadow-sm">
<div class="card-body">

<form method="POST" action="{{ route('admin.gaji.update', $user->id) }}">
@csrf

<div class="row">

{{-- GAJI POKOK --}}
<div class="col-md-6 mb-3">
<label>Gaji Pokok (Bulanan)</label>
<input type="number" id="gaji_pokok"
name="gaji_pokok"
class="form-control"
value="{{ old('gaji_pokok', $salary->gaji_pokok ?? 0) }}"
min="0" required>
</div>

{{-- MODE GAJI HARIAN --}}
<div class="col-md-6 mb-3">
<label>Mode Hitung Gaji Harian</label>
<select name="gaji_harian_mode"
id="mode"
class="form-control">
<option value="manual">Manual</option>
<option value="pokok">Dari Gaji Pokok</option>
<option value="pokok_plus_tunjangan">Pokok + Tunjangan</option>
</select>
</div>

{{-- GAJI HARIAN --}}
<div class="col-md-6 mb-3">
<label>Gaji Harian</label>
<input type="number"
id="gaji_harian"
name="gaji_harian"
class="form-control"
value="{{ old('gaji_harian', $salary->gaji_harian ?? 0) }}"
min="0">
<small class="text-muted">
Akan otomatis dihitung jika mode ≠ manual
</small>
</div>

{{-- LEMBUR --}}
<div class="col-md-6 mb-3">
<label>Lembur per Jam</label>
<input type="number"
name="lembur_per_jam"
class="form-control"
value="{{ old('lembur_per_jam', $salary->lembur_per_jam ?? 0) }}">
</div>

</div>

<hr>

<h5>Tunjangan</h5>

<div class="row">

@foreach([
'tunjangan_umum' => 'Tunjangan Umum',
'tunjangan_transport' => 'Tunjangan Transport',
'tunjangan_thr' => 'Tunjangan THR',
'tunjangan_kesehatan' => 'Tunjangan Kesehatan'
] as $field => $label)

<div class="col-md-6 mb-3">
<label>{{ $label }}</label>
<input type="number"
class="form-control tunjangan"
name="{{ $field }}"
value="{{ old($field, $salary->$field ?? 0) }}">
</div>

@endforeach

</div>

<hr>

<div class="form-check mb-2">
<input type="checkbox"
name="include_tunjangan"
class="form-check-input"
value="1"
{{ old('include_tunjangan', $salary->include_tunjangan ?? true) ? 'checked' : '' }}>
<label class="form-check-label">
Masukkan tunjangan ke payroll
</label>
</div>

<div class="form-check mb-4">
<input type="checkbox"
name="aktif"
class="form-check-input"
value="1"
{{ old('aktif', $salary->aktif ?? true) ? 'checked' : '' }}>
<label class="form-check-label">
Aktifkan payroll
</label>
</div>

<div class="d-flex justify-content-between">

<a href="{{ route('admin.gaji') }}" class="btn btn-secondary">
← Kembali
</a>

<button class="btn btn-primary">
💾 Simpan Gaji
</button>

</div>

</form>

</div>
</div>

</div>

{{-- AUTO CALC SCRIPT --}}
<script>

const hariKerja = 26;

function hitungHarian() {

let mode = document.getElementById("mode").value;

let pokok = parseFloat(document.getElementById("gaji_pokok").value || 0);

let tunjangan = 0;

document.querySelectorAll(".tunjangan").forEach(el => {
tunjangan += parseFloat(el.value || 0);
});

let hasil = 0;

if(mode === "pokok") {
hasil = pokok / hariKerja;
}

if(mode === "pokok_plus_tunjangan") {
hasil = (pokok + tunjangan) / hariKerja;
}

if(mode !== "manual") {
document.getElementById("gaji_harian").value = Math.round(hasil);
}

}

document.querySelectorAll("#mode, #gaji_pokok, .tunjangan")
.forEach(el => el.addEventListener("input", hitungHarian));

</script>

@endsection
