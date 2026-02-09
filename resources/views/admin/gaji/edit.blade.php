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
$selectedMode = old('gaji_harian_mode', $salary->gaji_harian_mode ?? 'manual');
$trainingEnabled = old('training_enabled', $salary->training_enabled ?? false);
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
<option value="manual" {{ $selectedMode === 'manual' ? 'selected' : '' }}>Manual</option>
<option value="pokok" {{ $selectedMode === 'pokok' ? 'selected' : '' }}>Dari Gaji Pokok</option>
<option value="pokok_plus_tunjangan" {{ $selectedMode === 'pokok_plus_tunjangan' ? 'selected' : '' }}>Pokok + Tunjangan</option>
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

<h5>Masa Training</h5>

<div class="form-check mb-3">
<input type="checkbox"
name="training_enabled"
id="training_enabled"
class="form-check-input"
value="1"
{{ $trainingEnabled ? 'checked' : '' }}>
<label class="form-check-label" for="training_enabled">
Aktifkan potongan masa training
</label>
</div>

<div id="training-config" class="border rounded p-3 mb-3">
<div class="row">

<div class="col-md-4 mb-3">
<label>Tanggal Mulai Training</label>
<input type="date"
name="training_start_date"
id="training_start_date"
class="form-control"
value="{{ old('training_start_date', optional($salary?->training_start_date)->format('Y-m-d')) }}">
</div>

<div class="col-md-4 mb-3">
<label>Durasi Training (Hari)</label>
<input type="number"
name="training_duration_days"
id="training_duration_days"
class="form-control"
min="1"
max="365"
value="{{ old('training_duration_days', $salary->training_duration_days ?? 0) }}">
</div>

<div class="col-md-4 mb-3">
<label>Jenis Potongan Training</label>
<select name="training_deduction_type"
id="training_deduction_type"
class="form-control">
<option value="percentage"
{{ old('training_deduction_type', $salary->training_deduction_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>
Persentase dari Gaji Harian (%)
</option>
<option value="fixed"
{{ old('training_deduction_type', $salary->training_deduction_type ?? '') === 'fixed' ? 'selected' : '' }}>
Nominal per Hari (Rp)
</option>
</select>
</div>

<div class="col-md-6 mb-0">
<label>Nilai Potongan Training</label>
<input type="number"
name="training_deduction_value"
id="training_deduction_value"
class="form-control"
step="0.01"
min="0"
value="{{ old('training_deduction_value', $salary->training_deduction_value ?? 0) }}">
<small class="text-muted" id="training_value_hint">
Isi nilai sesuai jenis potongan yang dipilih.
</small>
</div>

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

function toggleTrainingConfig() {
const enabled = document.getElementById("training_enabled").checked;
const config = document.getElementById("training-config");
const inputs = config.querySelectorAll("input, select");

config.style.opacity = enabled ? "1" : ".55";

inputs.forEach((el) => {
el.disabled = !enabled;
});

const hint = document.getElementById("training_value_hint");
const type = document.getElementById("training_deduction_type")?.value || "percentage";
if (hint) {
hint.textContent = type === "percentage"
? "Isi angka tanpa simbol %, contoh: 25 = (25% x gaji harian) x jumlah hari training aktif."
: "Isi nominal potongan per hari training (Rp/hari).";
}
}

document.getElementById("training_enabled")
.addEventListener("change", toggleTrainingConfig);

document.getElementById("training_deduction_type")
.addEventListener("change", toggleTrainingConfig);

toggleTrainingConfig();

</script>

@endsection
