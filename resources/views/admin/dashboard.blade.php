@extends('layouts.app')

@section('title', 'Dasbor')

@section('content')
@php
    $tingkatKehadiran = ($totalHadir + $totalKeterlambatan + $totalTidakMasuk) > 0
        ? round((($totalHadir + $totalKeterlambatan) / max($totalHadir + $totalKeterlambatan + $totalTidakMasuk, 1)) * 100, 1)
        : 0;

    $kartuUtama = [
        ['ikon' => 'fa-users', 'warna' => 'biru', 'judul' => 'Total Karyawan', 'nilai' => number_format($totalUser, 0, ',', '.'), 'catatan' => 'Karyawan aktif terdaftar', 'url' => route('admin.karyawan.index')],
        ['ikon' => 'fa-user-check', 'warna' => 'hijau', 'judul' => 'Total Absensi', 'nilai' => number_format($totalAbsensi, 0, ',', '.'), 'catatan' => 'Seluruh catatan absensi', 'url' => route('admin.absensi')],
        ['ikon' => 'fa-clock', 'warna' => 'kuning', 'judul' => 'Total Lembur', 'nilai' => number_format($totalLembur, 0, ',', '.'), 'catatan' => 'Pengajuan dan data lembur', 'url' => route('admin.lembur')],
        ['ikon' => 'fa-wallet', 'warna' => 'ungu', 'judul' => 'Karyawan Bergaji', 'nilai' => number_format($totalGaji, 0, ',', '.'), 'catatan' => 'Master gaji aktif', 'url' => route('admin.gaji')],
        ['ikon' => 'fa-calendar-check', 'warna' => 'toska', 'judul' => 'Tingkat Kehadiran', 'nilai' => number_format($tingkatKehadiran, 1, ',', '.') . '%', 'catatan' => ucfirst($periodeAbsensiLabel), 'url' => route('admin.laporan')],
        ['ikon' => 'fa-exclamation-circle', 'warna' => 'merah', 'judul' => 'Keterlambatan', 'nilai' => number_format($totalKeterlambatan, 0, ',', '.'), 'catatan' => number_format((float) $totalMenitTerlambat, 0, ',', '.') . ' menit', 'url' => route('admin.absensi')],
    ];

    $kartuRekrutmen = [
        ['label' => 'Menunggu', 'nilai' => $pelamarPending, 'ikon' => 'fa-hourglass-half', 'warna' => 'kuning'],
        ['label' => 'Ditinjau', 'nilai' => $pelamarReview, 'ikon' => 'fa-search', 'warna' => 'biru'],
        ['label' => 'Wawancara', 'nilai' => $pelamarInterview, 'ikon' => 'fa-comments', 'warna' => 'ungu'],
        ['label' => 'Pelatihan', 'nilai' => $pelamarTraining, 'ikon' => 'fa-chalkboard-teacher', 'warna' => 'toska'],
        ['label' => 'Diterima', 'nilai' => $pelamarAccepted, 'ikon' => 'fa-check-circle', 'warna' => 'hijau'],
        ['label' => 'Ditolak', 'nilai' => $pelamarRejected, 'ikon' => 'fa-times-circle', 'warna' => 'merah'],
    ];

    $kartuTugas = [
        ['label' => 'Total Tugas', 'nilai' => $jobTotal, 'warna' => 'biru'],
        ['label' => 'Terbuka', 'nilai' => $jobOpen, 'warna' => 'kuning'],
        ['label' => 'Dikerjakan', 'nilai' => $jobSedangDikerjakan, 'warna' => 'ungu'],
        ['label' => 'Selesai', 'nilai' => $jobSelesai, 'warna' => 'hijau'],
        ['label' => 'Ditutup', 'nilai' => $jobClosed, 'warna' => 'merah'],
    ];

    $distribusiPenempatan = [
        ['label' => 'SM Lecy', 'nilai' => $karyawanSMLeccy, 'warna' => 'biru'],
        ['label' => 'Gudang', 'nilai' => $karyawanGudang, 'warna' => 'kuning'],
        ['label' => 'Percetakan', 'nilai' => $karyawanSMPenempatan, 'warna' => 'toska'],
    ];

    $labelStatus = fn (?string $status) => match ($status) {
        'pending', 'requested' => 'Menunggu',
        'approved' => 'Disetujui',
        'finished', 'done' => 'Selesai',
        'closed' => 'Ditutup',
        'rejected' => 'Ditolak',
        default => $status ? ucfirst($status) : '-',
    };
@endphp

<div class="container-fluid dashboard-pro">
    <div class="panel-atas">
        <div>
            <span class="label-halaman">Dasbor</span>
            <h1>Selamat datang, {{ auth()->user()->name }}</h1>
            <p>Ringkasan kondisi karyawan, absensi, rekrutmen, tugas kerja, dan payroll pada sistem.</p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-periode">
            <label for="bulan">Periode</label>
            <div class="input-filter">
                <input type="month" id="bulan" name="bulan" value="{{ $selectedBulan }}" class="form-control">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light">Atur Ulang</a>
            </div>
        </form>
    </div>

    <div class="panel-wawasan">
        <div class="ikon-wawasan">
            <i class="fas fa-magic"></i>
        </div>
        <div class="isi-wawasan">
            <strong>Ada {{ $karyawanTerlambat + $karyawanTidakMasuk }} catatan yang perlu diperhatikan pada {{ ucfirst($periodeAbsensiLabel) }}</strong>
            <span>{{ $karyawanTerlambat }} karyawan pernah terlambat, {{ $karyawanTidakMasuk }} karyawan memiliki hari tidak masuk, dan {{ $totalKeterlambatan }} kejadian keterlambatan tercatat.</span>
        </div>
        <a href="{{ route('admin.laporan') }}" class="btn btn-light">
            <i class="fas fa-file-alt mr-1"></i> Lihat Laporan
        </a>
    </div>

    <div class="grid-metrik">
        @foreach($kartuUtama as $kartu)
            <a href="{{ $kartu['url'] }}" class="kartu-metrik aksen-{{ $kartu['warna'] }}">
                <span class="ikon-metrik"><i class="fas {{ $kartu['ikon'] }}"></i></span>
                <span class="judul-metrik">{{ $kartu['judul'] }}</span>
                <strong>{{ $kartu['nilai'] }}</strong>
                <small>{{ $kartu['catatan'] }}</small>
            </a>
        @endforeach
    </div>

    <div class="grid-utama">
        <section class="panel-data panel-besar">
            <div class="panel-judul">
                <div>
                    <h2>Disiplin Karyawan</h2>
                    <p>{{ ucfirst($periodeAbsensiLabel) }}</p>
                </div>
            </div>
            <div class="grid-disiplin">
                <div class="kartu-ringkas aksen-kuning">
                    <span>Karyawan Terlambat</span>
                    <strong>{{ $karyawanTerlambat }}</strong>
                    <small>Pernah telat minimal 1 kali</small>
                </div>
                <div class="kartu-ringkas aksen-merah">
                    <span>Total Keterlambatan</span>
                    <strong>{{ $totalKeterlambatan }}x</strong>
                    <small>{{ number_format((float) $totalMenitTerlambat, 0, ',', '.') }} menit</small>
                </div>
                <div class="kartu-ringkas aksen-hijau">
                    <span>Tidak Pernah Terlambat</span>
                    <strong>{{ $karyawanTidakPernahTerlambat }}</strong>
                    <small>Dari {{ $totalUser }} karyawan</small>
                </div>
                <div class="kartu-ringkas aksen-biru">
                    <span>Karyawan Tidak Masuk</span>
                    <strong>{{ $karyawanTidakMasuk }}</strong>
                    <small>{{ $totalTidakMasuk }} total hari</small>
                </div>
            </div>

            <div class="grafik-dua-kolom">
                <div class="kanvas-grafik">
                    <canvas id="disciplineComparisonChart"></canvas>
                </div>
                <div class="kanvas-grafik">
                    <canvas id="attendanceEventChart"></canvas>
                </div>
            </div>
        </section>

        <section class="panel-data">
            <div class="panel-judul">
                <div>
                    <h2>Distribusi Penempatan</h2>
                    <p>Karyawan berdasarkan lokasi kerja</p>
                </div>
            </div>
            <div class="daftar-distribusi">
                @foreach($distribusiPenempatan as $item)
                    @php($persen = $totalUser > 0 ? round(($item['nilai'] / max($totalUser, 1)) * 100) : 0)
                    <div class="baris-distribusi aksen-{{ $item['warna'] }}">
                        <div>
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['nilai'] }} karyawan</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $persen }}%"></div>
                        </div>
                        <small>{{ $persen }}%</small>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid-utama">
        <section class="panel-data panel-besar">
            <div class="panel-judul">
                <div>
                    <h2>Pengawasan Keterlambatan</h2>
                    <p>5 karyawan dengan keterlambatan terbanyak</p>
                </div>
                <a href="{{ route('admin.absensi') }}" class="btn btn-light btn-sm">Lihat Absensi</a>
            </div>

            <div class="table-responsive">
                <table class="table tabel-modern mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Nama</th>
                            <th style="width:180px;">Jumlah Telat</th>
                            <th style="width:200px;">Akumulasi Menit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topKaryawanTerlambat as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $item->user->name ?? '-' }}</strong></td>
                                <td><span class="badge badge-warning">{{ (int) $item->total_terlambat }}x</span></td>
                                <td>{{ number_format((float) $item->total_menit, 0, ',', '.') }} menit</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Belum ada data keterlambatan pada periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel-data">
            <div class="panel-judul">
                <div>
                    <h2>Status Rekrutmen</h2>
                    <p>Perkembangan pelamar pekerjaan</p>
                </div>
            </div>
            <div class="grid-mini">
                @foreach($kartuRekrutmen as $item)
                    <div class="kartu-mini aksen-{{ $item['warna'] }}">
                        <i class="fas {{ $item['ikon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['nilai'] }}</strong>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid-utama">
        <section class="panel-data panel-besar">
            <div class="panel-judul">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>Absensi dan lembur pada {{ ucfirst($periodeAbsensiLabel) }}</p>
                </div>
            </div>

            <div class="grid-aktivitas">
                <div class="table-responsive">
                    <table class="table tabel-modern mb-0">
                        <thead>
                            <tr>
                                <th colspan="4">Absensi Terbaru</th>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <th>Jam</th>
                                <th>Aksi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($absensiTerbaru as $item)
                                <tr>
                                    <td>{{ $item->user->name ?? '-' }}</td>
                                    <td>{{ $item->jam_tampil }}</td>
                                    <td><span class="badge badge-{{ $item->aksi_badge }}">{{ $item->aksi_label }}</span></td>
                                    <td><span class="badge badge-{{ $item->status_badge }}">{{ $item->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive">
                    <table class="table tabel-modern mb-0">
                        <thead>
                            <tr>
                                <th colspan="3">Lembur Terbaru</th>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lemburTerbaru as $item)
                                <tr>
                                    <td>{{ $item->user->name ?? '-' }}</td>
                                    <td>{{ $item->tanggal }}</td>
                                    <td><span class="badge badge-warning">{{ $labelStatus($item->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel-data">
            <div class="panel-judul">
                <div>
                    <h2>Tugas Kerja</h2>
                    <p>Ringkasan status tugas karyawan</p>
                </div>
            </div>
            <div class="daftar-tugas">
                @foreach($kartuTugas as $item)
                    <div class="baris-tugas aksen-{{ $item['warna'] }}">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ $item['nilai'] }}</strong>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
.dashboard-pro {
    --panel-border: #e5ebf3;
    --panel-shadow: 0 14px 36px rgba(20, 35, 60, .08);
    --soft-blue: #eef5ff;
    --soft-green: #ecf8f3;
    --soft-yellow: #fff7e7;
    --soft-red: #fff0ee;
    --soft-purple: #f4f0ff;
    --soft-teal: #edfafa;
}

.dashboard-pro .panel-atas {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 18px;
}

.dashboard-pro .label-halaman {
    color: #426083;
    display: inline-flex;
    font-size: .88rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.dashboard-pro h1 {
    font-size: 1.55rem;
    font-weight: 800;
    margin: 0 0 4px;
}

.dashboard-pro p {
    color: var(--text-muted);
    margin: 0;
}

.dashboard-pro .filter-periode,
.dashboard-pro .kartu-metrik,
.dashboard-pro .panel-data,
.dashboard-pro .kartu-ringkas,
.dashboard-pro .kartu-mini,
.dashboard-pro .baris-tugas {
    background: var(--surface);
    border: 1px solid var(--panel-border);
    border-radius: 14px;
    box-shadow: var(--panel-shadow);
}

.dashboard-pro .filter-periode {
    padding: 12px;
}

.dashboard-pro .filter-periode label {
    color: var(--text-muted);
    display: block;
    font-size: .78rem;
    font-weight: 700;
    margin-bottom: 6px;
}

.dashboard-pro .input-filter {
    display: flex;
    gap: 8px;
}

.dashboard-pro .input-filter input {
    min-width: 155px;
}

.dashboard-pro .panel-wawasan {
    align-items: center;
    background: linear-gradient(120deg, #f3f7ff 0%, #f8f4ff 60%, #fff4fa 100%);
    border: 1px solid #d9e4ff;
    border-radius: 16px;
    box-shadow: var(--panel-shadow);
    display: flex;
    gap: 14px;
    margin-bottom: 18px;
    padding: 16px;
}

.dashboard-pro .ikon-wawasan {
    align-items: center;
    background: linear-gradient(135deg, #4b6fe8 0%, #a45bd8 100%);
    border-radius: 12px;
    color: #fff;
    display: inline-flex;
    height: 44px;
    justify-content: center;
    width: 44px;
}

.dashboard-pro .isi-wawasan {
    flex: 1;
}

.dashboard-pro .isi-wawasan strong,
.dashboard-pro .isi-wawasan span {
    display: block;
}

.dashboard-pro .isi-wawasan strong {
    color: #263858;
    margin-bottom: 3px;
}

.dashboard-pro .isi-wawasan span {
    color: #5b6982;
}

.dashboard-pro .grid-metrik {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    margin-bottom: 18px;
}

.dashboard-pro .kartu-metrik {
    color: var(--text);
    display: flex;
    flex-direction: column;
    min-height: 142px;
    padding: 15px;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease;
}

.dashboard-pro .kartu-metrik:hover {
    color: var(--text);
    transform: translateY(-2px);
}

.dashboard-pro .ikon-metrik {
    align-items: center;
    border-radius: 10px;
    display: inline-flex;
    height: 38px;
    justify-content: center;
    margin-bottom: 14px;
    width: 38px;
}

.dashboard-pro .judul-metrik,
.dashboard-pro .kartu-ringkas span,
.dashboard-pro .kartu-mini span,
.dashboard-pro .baris-tugas span {
    color: var(--text-muted);
    font-size: .84rem;
    font-weight: 700;
}

.dashboard-pro .kartu-metrik strong {
    font-size: 1.45rem;
    line-height: 1;
    margin-bottom: 8px;
}

.dashboard-pro .kartu-metrik small {
    color: var(--text-muted);
}

.dashboard-pro .grid-utama {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 2.1fr) minmax(300px, .9fr);
    margin-bottom: 18px;
}

.dashboard-pro .panel-data {
    overflow: hidden;
}

.dashboard-pro .panel-judul {
    align-items: center;
    border-bottom: 1px solid var(--panel-border);
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 16px 18px;
}

.dashboard-pro .panel-judul h2 {
    border-left: 4px solid #3b6eea;
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0 0 3px;
    padding-left: 10px;
}

.dashboard-pro .panel-judul p {
    font-size: .9rem;
    padding-left: 14px;
}

.dashboard-pro .grid-disiplin,
.dashboard-pro .grid-mini {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding: 16px 18px;
}

.dashboard-pro .kartu-ringkas,
.dashboard-pro .kartu-mini,
.dashboard-pro .baris-tugas {
    box-shadow: none;
}

.dashboard-pro .kartu-ringkas {
    padding: 14px;
}

.dashboard-pro .kartu-ringkas strong {
    display: block;
    font-size: 1.4rem;
    margin: 7px 0 4px;
}

.dashboard-pro .grafik-dua-kolom {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding: 0 18px 18px;
}

.dashboard-pro .kanvas-grafik {
    border: 1px solid var(--panel-border);
    border-radius: 14px;
    height: 260px;
    padding: 12px;
}

.dashboard-pro .daftar-distribusi,
.dashboard-pro .daftar-tugas {
    display: grid;
    gap: 12px;
    padding: 16px 18px 18px;
}

.dashboard-pro .baris-distribusi {
    display: grid;
    gap: 8px;
}

.dashboard-pro .baris-distribusi > div:first-child {
    display: flex;
    gap: 8px;
    justify-content: space-between;
}

.dashboard-pro .baris-distribusi span {
    color: var(--text);
    font-weight: 800;
}

.dashboard-pro .baris-distribusi strong,
.dashboard-pro .baris-distribusi small {
    color: var(--text-muted);
    font-size: .84rem;
}

.dashboard-pro .progress {
    background: #edf2f8;
    border-radius: 999px;
    height: 9px;
}

.dashboard-pro .progress-bar {
    border-radius: 999px;
}

.dashboard-pro .tabel-modern thead th {
    background: #f6f8fb;
    border-bottom: 1px solid var(--panel-border);
    color: #53657f;
    font-size: .78rem;
    text-transform: uppercase;
}

.dashboard-pro .tabel-modern td,
.dashboard-pro .tabel-modern th {
    padding: 13px 16px;
    vertical-align: middle;
}

.dashboard-pro .grid-aktivitas {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    padding: 16px 18px 18px;
}

.dashboard-pro .kartu-mini {
    min-height: 92px;
    padding: 12px;
}

.dashboard-pro .kartu-mini i {
    margin-bottom: 8px;
}

.dashboard-pro .kartu-mini strong {
    display: block;
    font-size: 1.25rem;
    margin-top: 3px;
}

.dashboard-pro .baris-tugas {
    align-items: center;
    display: flex;
    justify-content: space-between;
    padding: 13px 14px;
}

.dashboard-pro .baris-tugas strong {
    font-size: 1.2rem;
}

.dashboard-pro .aksen-biru .ikon-metrik,
.dashboard-pro .aksen-biru.kartu-ringkas,
.dashboard-pro .aksen-biru.kartu-mini { background: var(--soft-blue); color: #315fce; }
.dashboard-pro .aksen-hijau .ikon-metrik,
.dashboard-pro .aksen-hijau.kartu-ringkas,
.dashboard-pro .aksen-hijau.kartu-mini { background: var(--soft-green); color: #24865d; }
.dashboard-pro .aksen-kuning .ikon-metrik,
.dashboard-pro .aksen-kuning.kartu-ringkas,
.dashboard-pro .aksen-kuning.kartu-mini { background: var(--soft-yellow); color: #bb7a17; }
.dashboard-pro .aksen-merah .ikon-metrik,
.dashboard-pro .aksen-merah.kartu-ringkas,
.dashboard-pro .aksen-merah.kartu-mini { background: var(--soft-red); color: #bf563c; }
.dashboard-pro .aksen-ungu .ikon-metrik,
.dashboard-pro .aksen-ungu.kartu-ringkas,
.dashboard-pro .aksen-ungu.kartu-mini { background: var(--soft-purple); color: #6654cf; }
.dashboard-pro .aksen-toska .ikon-metrik,
.dashboard-pro .aksen-toska.kartu-ringkas,
.dashboard-pro .aksen-toska.kartu-mini { background: var(--soft-teal); color: #1f7c7c; }

.dashboard-pro .aksen-biru .progress-bar,
.dashboard-pro .aksen-biru.baris-tugas { background: #3f6fea; color: #fff; }
.dashboard-pro .aksen-hijau .progress-bar,
.dashboard-pro .aksen-hijau.baris-tugas { background: #2b9a67; color: #fff; }
.dashboard-pro .aksen-kuning .progress-bar,
.dashboard-pro .aksen-kuning.baris-tugas { background: #e0a025; color: #fff; }
.dashboard-pro .aksen-merah .progress-bar,
.dashboard-pro .aksen-merah.baris-tugas { background: #d45d45; color: #fff; }
.dashboard-pro .aksen-ungu .progress-bar,
.dashboard-pro .aksen-ungu.baris-tugas { background: #6a5ce0; color: #fff; }
.dashboard-pro .aksen-toska .progress-bar,
.dashboard-pro .aksen-toska.baris-tugas { background: #249393; color: #fff; }
.dashboard-pro .baris-tugas span { color: rgba(255, 255, 255, .86); }

:root[data-theme='dark'] .dashboard-pro {
    --panel-border: #2c3b52;
    --panel-shadow: 0 14px 36px rgba(0, 0, 0, .28);
    --soft-blue: #1b315d;
    --soft-green: #1d3a30;
    --soft-yellow: #3c3020;
    --soft-red: #3b2928;
    --soft-purple: #2d284f;
    --soft-teal: #173c3e;
}

:root[data-theme='dark'] .dashboard-pro .panel-wawasan {
    background: linear-gradient(120deg, #1c2a42 0%, #282747 55%, #3a2537 100%);
    border-color: #334464;
}

:root[data-theme='dark'] .dashboard-pro .isi-wawasan strong { color: #eef4ff; }
:root[data-theme='dark'] .dashboard-pro .isi-wawasan span,
:root[data-theme='dark'] .dashboard-pro .label-halaman { color: #afbdd4; }
:root[data-theme='dark'] .dashboard-pro .tabel-modern thead th { background: #202c3d; color: #b8c7de; }
:root[data-theme='dark'] .dashboard-pro .progress { background: #263449; }

@media (max-width: 1199.98px) {
    .dashboard-pro .grid-metrik { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .dashboard-pro .grid-utama { grid-template-columns: 1fr; }
}

@media (max-width: 767.98px) {
    .dashboard-pro .panel-atas,
    .dashboard-pro .panel-wawasan,
    .dashboard-pro .input-filter {
        align-items: stretch;
        flex-direction: column;
    }

    .dashboard-pro .grid-metrik,
    .dashboard-pro .grid-disiplin,
    .dashboard-pro .grafik-dua-kolom,
    .dashboard-pro .grid-aktivitas {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const disciplineCtx = document.getElementById('disciplineComparisonChart');
const attendanceCtx = document.getElementById('attendanceEventChart');
const chartTextColor = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim() || '#6b7280';
const chartGridColor = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || '#e5e7eb';

if (disciplineCtx) {
    new Chart(disciplineCtx, {
        type: 'doughnut',
        data: {
            labels: ['Karyawan Terlambat', 'Tidak Pernah Terlambat'],
            datasets: [{
                data: @json([$karyawanTerlambat, $karyawanTidakPernahTerlambat]),
                backgroundColor: ['#e0a025', '#2b9a67'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: chartTextColor,
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            },
            cutout: '68%'
        }
    });
}

if (attendanceCtx) {
    new Chart(attendanceCtx, {
        type: 'bar',
        data: {
            labels: ['Hadir', 'Terlambat', 'Tidak Masuk'],
            datasets: [{
                label: 'Jumlah Kejadian',
                data: @json([$totalHadir, $totalKeterlambatan, $totalTidakMasuk]),
                backgroundColor: ['#3f6fea', '#e0a025', '#d45d45'],
                borderRadius: 10,
                maxBarThickness: 56
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: chartTextColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: chartGridColor },
                    ticks: {
                        color: chartTextColor,
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>
@endpush
