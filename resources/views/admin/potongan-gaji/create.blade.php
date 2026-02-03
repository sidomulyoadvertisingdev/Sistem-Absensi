@extends('layouts.app')

@section('title','Tambah Aturan Potongan Gaji')

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 font-weight-bold">Tambah Aturan Potongan Gaji</h5>
        </div>

        <form method="POST" action="{{ route('admin.potongan-gaji.store') }}">
            @csrf

            <div class="card-body">

                {{-- ================= KODE ================= --}}
                <div class="form-group">
                    <label>Kode Aturan</label>
                    <input type="text"
                           name="kode"
                           class="form-control"
                           placeholder="Contoh: POT_TUNJANGAN"
                           required>
                </div>

                {{-- ================= NAMA ================= --}}
                <div class="form-group">
                    <label>Nama Aturan</label>
                    <input type="text"
                           name="nama"
                           class="form-control"
                           placeholder="Contoh: Potongan Tunjangan Transport"
                           required>
                </div>

                {{-- ================= KETERANGAN ================= --}}
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan"
                              class="form-control"
                              rows="2"></textarea>
                </div>

                <hr>

                {{-- ================= JENIS POTONGAN ================= --}}
                <div class="form-group">
                    <label>Jenis Potongan</label>
                    <select name="type" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="fixed">Nominal (Rp)</option>
                        <option value="percentage">Persentase (%)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nilai Potongan</label>
                    <input type="number"
                           step="0.01"
                           name="value"
                           class="form-control"
                           required>
                </div>

                <hr>

                {{-- ================= DASAR POTONGAN ================= --}}
                <div class="form-group">
                    <label>Potongan Dihitung Dari</label>
                    <select name="base_source"
                            id="base_source"
                            class="form-control"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="gaji_pokok">Gaji Pokok</option>
                        <option value="tunjangan">Tunjangan</option>
                        <option value="total_gaji">Total Gaji</option>
                    </select>
                </div>

                {{-- ================= PILIH TUNJANGAN ================= --}}
                <div class="form-group d-none" id="tunjangan-box">
                    <label>Jenis Tunjangan yang Dipotong</label>

                    <div class="border rounded p-3">
                        @forelse($tunjangans as $key => $label)
                            <div class="form-check">
                                <input type="checkbox"
                                       name="tunjangan_items[]"
                                       value="{{ $key }}"
                                       class="form-check-input"
                                       id="tj_{{ $key }}">
                                <label for="tj_{{ $key }}" class="form-check-label">
                                    {{ $label }}
                                </label>
                            </div>
                        @empty
                            <div class="text-muted">
                                Belum ada tunjangan di struktur gaji
                            </div>
                        @endforelse
                    </div>

                    <small class="text-muted">
                        Tunjangan diambil dari struktur gaji karyawan
                    </small>
                </div>

                <hr>

                {{-- ================= KONDISI ================= --}}
                <div class="form-group">
                    <label>Jenis Kondisi</label>
                    <select name="condition_type" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="off_day">Tidak Masuk</option>
                        <option value="pelanggaran">Pelanggaran</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Trigger Minimal Kejadian</label>
                    <input type="number"
                           name="condition_value"
                           class="form-control"
                           value="1"
                           min="1">
                </div>

                <hr>

                {{-- ================= PENEMPATAN ================= --}}
                <div class="form-group">
                    <label class="font-weight-bold">Berlaku Untuk Penempatan</label>

                    <div class="border rounded p-3">
                        <div class="row">
                            @foreach($penempatans as $p)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        {{-- ❌ TIDAK PAKAI required --}}
                                        <input type="checkbox"
                                               name="penempatan[]"
                                               value="{{ $p }}"
                                               class="form-check-input"
                                               id="p_{{ $loop->index }}">
                                        <label for="p_{{ $loop->index }}"
                                               class="form-check-label">
                                            {{ $p }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <small class="text-muted d-block mt-2">
                            Pilih minimal satu penempatan (validasi di server)
                        </small>

                        {{-- ERROR MESSAGE --}}
                        @error('penempatan')
                            <div class="text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="form-group form-check">
                    <input type="checkbox"
                           name="aktif"
                           value="1"
                           class="form-check-input"
                           checked>
                    <label class="form-check-label">
                        Aktif
                    </label>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.potongan-gaji.index') }}"
                   class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit"
                        class="btn btn-primary">
                    Simpan Aturan
                </button>
            </div>

        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
const baseSelect   = document.getElementById('base_source');
const tunjanganBox = document.getElementById('tunjangan-box');

function toggleTunjangan() {
    tunjanganBox.classList.toggle(
        'd-none',
        baseSelect.value !== 'tunjangan'
    );
}

baseSelect.addEventListener('change', toggleTunjangan);
toggleTunjangan();
</script>
@endpush
