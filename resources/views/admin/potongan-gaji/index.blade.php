@extends('layouts.app')

@section('title','Aturan Potongan Gaji')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Aturan Potongan Gaji</h4>
        <a href="{{ route('admin.potongan-gaji.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Aturan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-sm table-hover text-center mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Aturan</th>
                        <th>Jenis Potongan</th>
                        <th>Nilai</th>
                        <th>Basis Perhitungan</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rules as $rule)
                    <tr>

                        {{-- KODE --}}
                        <td>
                            <span class="badge badge-info">
                                {{ $rule->kode }}
                            </span>
                        </td>

                        {{-- NAMA --}}
                        <td class="text-left">
                            <strong>{{ $rule->nama }}</strong>
                            @if($rule->keterangan)
                                <br>
                                <small class="text-muted">
                                    {{ $rule->keterangan }}
                                </small>
                            @endif
                        </td>

                        {{-- JENIS POTONGAN --}}
                        <td>
                            @if($rule->type === 'percentage')
                                <span class="badge badge-warning">Persentase (%)</span>
                            @else
                                <span class="badge badge-secondary">Nominal</span>
                            @endif
                        </td>

                        {{-- NILAI --}}
                        <td class="text-right">
                            @if($rule->type === 'percentage')
                                {{ $rule->value }} %
                            @else
                                Rp {{ number_format($rule->value, 0, ',', '.') }}
                            @endif
                        </td>

                        {{-- BASIS PERHITUNGAN --}}
                        <td>
                            @switch($rule->base_amount)
                                @case('gaji_pokok')
                                    Gaji Pokok
                                    @break
                                @case('salary_kotor')
                                    Gaji Kotor
                                    @break
                                @case('total_gaji')
                                    Total Gaji
                                    @break
                                @default
                                    -
                            @endswitch
                        </td>

                        {{-- KONDISI --}}
                        <td>
                            {{ ucfirst($rule->condition_type) }}
                            <br>
                            <small class="text-muted">
                                Batas: {{ $rule->condition_value }}
                            </small>
                        </td>

                        {{-- STATUS --}}
                        <td>
                            @if($rule->aktif)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td>
                            <a href="{{ route('admin.potongan-gaji.edit', $rule) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.potongan-gaji.destroy', $rule) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                        onclick="return confirm('Hapus aturan ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-muted py-3">
                            Belum ada aturan potongan gaji
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
