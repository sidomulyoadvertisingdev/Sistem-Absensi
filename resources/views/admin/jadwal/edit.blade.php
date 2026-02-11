@extends('layouts.app')

@section('title', 'Atur Jadwal Kerja')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-4">
        <h1 class="mb-0">Atur Jadwal Kerja</h1>
        <small class="text-muted">
            Karyawan: <strong>{{ $user->name }}</strong>
        </small>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $hariList = [
            'senin'  => 'Senin',
            'selasa' => 'Selasa',
            'rabu'   => 'Rabu',
            'kamis'  => 'Kamis',
            'jumat'  => 'Jumat',
            'sabtu'  => 'Sabtu',
            'minggu' => 'Minggu',
        ];
        $mode = $user->schedule_mode ?? 'per_hari';
    @endphp

    <form method="POST" action="{{ route('admin.jadwal.update', $user->id) }}">
        @csrf

        <div class="card">
            <div class="card-body">

                {{-- MODE JADWAL --}}
                <div class="mb-4">
                    <label class="font-weight-bold d-block">Mode Jadwal</label>
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               name="schedule_mode"
                               id="mode_per_hari"
                               value="per_hari"
                               {{ $mode === 'per_hari' ? 'checked' : '' }}>
                        <label class="form-check-label" for="mode_per_hari">
                            Per Hari (jadwal tetap mingguan)
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               name="schedule_mode"
                               id="mode_per_tanggal"
                               value="per_tanggal"
                               {{ $mode === 'per_tanggal' ? 'checked' : '' }}>
                        <label class="form-check-label" for="mode_per_tanggal">
                            Per Tanggal (jadwal fleksibel)
                        </label>
                    </div>
                </div>

                {{-- JADWAL PER HARI --}}
                <div id="jadwal-per-hari">
                @foreach($hariList as $key => $label)

                    @php
                        $dataJadwal = $jadwal[$key] ?? null;
                    @endphp

                    <div class="border rounded p-3 mb-3">

                        {{-- CHECKBOX --}}
                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="hari[{{ $key }}]"
                                   id="hari_{{ $key }}"
                                   {{ ($dataJadwal && $dataJadwal->aktif) ? 'checked' : '' }}>

                            <label class="form-check-label font-weight-bold"
                                   for="hari_{{ $key }}">
                                {{ $label }}
                            </label>

                            <small class="text-muted ml-2">
                                (tidak dicentang = libur)
                            </small>
                        </div>

                        {{-- JAM --}}
                        <div class="row">

                            <div class="col-md-3 mb-2">
                                <label>Jam Masuk</label>
                                <input type="time"
                                       name="jam_masuk[{{ $key }}]"
                                       class="form-control"
                                       value="{{ old("jam_masuk.$key", $dataJadwal->jam_masuk ?? '') }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Jam Pulang</label>
                                <input type="time"
                                       name="jam_pulang[{{ $key }}]"
                                       class="form-control"
                                       value="{{ old("jam_pulang.$key", $dataJadwal->jam_pulang ?? '') }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Istirahat Mulai</label>
                                <input type="time"
                                       name="istirahat_mulai[{{ $key }}]"
                                       class="form-control"
                                       value="{{ old("istirahat_mulai.$key", $dataJadwal->istirahat_mulai ?? '') }}">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Istirahat Selesai</label>
                                <input type="time"
                                       name="istirahat_selesai[{{ $key }}]"
                                       class="form-control"
                                       value="{{ old("istirahat_selesai.$key", $dataJadwal->istirahat_selesai ?? '') }}">
                            </div>

                        </div>

                    </div>

                @endforeach
                </div>

                {{-- JADWAL PER TANGGAL --}}
                <div id="jadwal-per-tanggal" class="mt-4">
                    <div class="alert alert-info">
                        Atur jadwal berdasarkan tanggal. Jika tidak aktif, dianggap libur.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small">
                            Tambahkan beberapa tanggal sekaligus sebelum disimpan.
                        </div>
                        <button type="button" class="btn btn-sm btn-light" id="add-tanggal-row">
                            <i class="fas fa-plus"></i> Tambah Tanggal
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Istirahat Mulai</th>
                                    <th>Istirahat Selesai</th>
                                    <th>Aktif</th>
                                    <th>Hapus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwalTanggal as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="date"
                                                   name="tanggal_tgl[{{ $index }}]"
                                                   class="form-control"
                                                   value="{{ old("tanggal_tgl.$index", optional($item->tanggal)->format('Y-m-d')) }}"
                                                   readonly>
                                        </td>
                                        <td>
                                            <input type="time"
                                                   name="jam_masuk_tgl[{{ $index }}]"
                                                   class="form-control"
                                                   value="{{ old("jam_masuk_tgl.$index", $item->jam_masuk ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="time"
                                                   name="jam_pulang_tgl[{{ $index }}]"
                                                   class="form-control"
                                                   value="{{ old("jam_pulang_tgl.$index", $item->jam_pulang ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="time"
                                                   name="istirahat_mulai_tgl[{{ $index }}]"
                                                   class="form-control"
                                                   value="{{ old("istirahat_mulai_tgl.$index", $item->istirahat_mulai ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="time"
                                                   name="istirahat_selesai_tgl[{{ $index }}]"
                                                   class="form-control"
                                                   value="{{ old("istirahat_selesai_tgl.$index", $item->istirahat_selesai ?? '') }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="aktif_tgl[{{ $index }}]"
                                                   {{ old("aktif_tgl.$index", $item->aktif ?? false) ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                   name="hapus_tgl[{{ $index }}]">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Belum ada jadwal per tanggal
                                        </td>
                                    </tr>
                                @endforelse

                                @php $newKey = 'new'; @endphp
                                <tr class="table-light">
                                    <td>
                                        <input type="date"
                                               name="tanggal_tgl[{{ $newKey }}]"
                                               class="form-control"
                                               value="{{ old("tanggal_tgl.$newKey") }}">
                                    </td>
                                    <td>
                                        <input type="time"
                                               name="jam_masuk_tgl[{{ $newKey }}]"
                                               class="form-control"
                                               value="{{ old("jam_masuk_tgl.$newKey") }}">
                                    </td>
                                    <td>
                                        <input type="time"
                                               name="jam_pulang_tgl[{{ $newKey }}]"
                                               class="form-control"
                                               value="{{ old("jam_pulang_tgl.$newKey") }}">
                                    </td>
                                    <td>
                                        <input type="time"
                                               name="istirahat_mulai_tgl[{{ $newKey }}]"
                                               class="form-control"
                                               value="{{ old("istirahat_mulai_tgl.$newKey") }}">
                                    </td>
                                    <td>
                                        <input type="time"
                                               name="istirahat_selesai_tgl[{{ $newKey }}]"
                                               class="form-control"
                                               value="{{ old("istirahat_selesai_tgl.$newKey") }}">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                               name="aktif_tgl[{{ $newKey }}]"
                                               {{ old("aktif_tgl.$newKey", true) ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center text-muted">
                                        -
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Centang Hapus untuk menghapus jadwal pada tanggal tersebut.
                    </small>
                </div>

                {{-- BUTTON --}}
                <div class="d-flex justify-content-between mt-4">

                    <a href="{{ route('admin.jadwal') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Jadwal
                    </button>

                </div>

            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    (function () {
        var perHari = document.getElementById('mode_per_hari');
        var perTanggal = document.getElementById('mode_per_tanggal');
        var sectionHari = document.getElementById('jadwal-per-hari');
        var sectionTanggal = document.getElementById('jadwal-per-tanggal');
        var addBtn = document.getElementById('add-tanggal-row');
        var tanggalTable = sectionTanggal
            ? sectionTanggal.querySelector('table tbody')
            : null;
        var newRowIndex = 0;

        function toggleMode() {
            var isPerHari = perHari && perHari.checked;
            if (sectionHari) {
                sectionHari.style.display = isPerHari ? 'block' : 'none';
            }
            if (sectionTanggal) {
                sectionTanggal.style.display = isPerHari ? 'none' : 'block';
            }
        }

        if (perHari && perTanggal) {
            perHari.addEventListener('change', toggleMode);
            perTanggal.addEventListener('change', toggleMode);
            toggleMode();
        }

        function buildNewTanggalRow(index) {
            var tr = document.createElement('tr');
            tr.className = 'table-light';
            tr.innerHTML =
                '<td>' +
                    '<input type="date" name="tanggal_tgl[' + index + ']" class="form-control">' +
                '</td>' +
                '<td>' +
                    '<input type="time" name="jam_masuk_tgl[' + index + ']" class="form-control">' +
                '</td>' +
                '<td>' +
                    '<input type="time" name="jam_pulang_tgl[' + index + ']" class="form-control">' +
                '</td>' +
                '<td>' +
                    '<input type="time" name="istirahat_mulai_tgl[' + index + ']" class="form-control">' +
                '</td>' +
                '<td>' +
                    '<input type="time" name="istirahat_selesai_tgl[' + index + ']" class="form-control">' +
                '</td>' +
                '<td class="text-center">' +
                    '<input type="checkbox" name="aktif_tgl[' + index + ']" checked>' +
                '</td>' +
                '<td class="text-center text-muted">-</td>';
            return tr;
        }

        if (addBtn && tanggalTable) {
            addBtn.addEventListener('click', function () {
                var index = 'new_' + Date.now() + '_' + (newRowIndex++);
                tanggalTable.appendChild(buildNewTanggalRow(index));
            });
        }
    })();
</script>
@endpush
@endsection
