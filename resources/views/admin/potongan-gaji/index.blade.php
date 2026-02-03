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
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead class="thead-dark text-center">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Aturan</th>
                        <th>Jenis</th>
                        <th>Nilai</th>
                        <th>Basis Potongan</th>
                        <th>Penempatan</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rules as $rule)
                    <tr>

                        {{-- KODE --}}
                        <td class="text-center">
                            <span class="badge badge-info">
                                {{ $rule->kode }}
                            </span>
                        </td>

                        {{-- NAMA --}}
                        <td>
                            <strong>{{ $rule->nama }}</strong>
                            @if($rule->keterangan)
                                <br>
                                <small class="text-muted">
                                    {{ $rule->keterangan }}
                                </small>
                            @endif
                        </td>

                        {{-- JENIS POTONGAN --}}
                        <td class="text-center">
                            @if($rule->type === 'percentage')
                                <span class="badge badge-warning">Persentase</span>
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
                        <td class="text-center">
                            @switch($rule->base_source)
                                @case('gaji_pokok')
                                    <span class="badge badge-primary">Gaji Pokok</span>
                                    @break
                                @case('tunjangan')
                                    <span class="badge badge-info">Tunjangan</span>
                                    @break
                                @case('total_gaji')
                                    <span class="badge badge-dark">Total Gaji</span>
                                    @break
                                @default
                                    <span class="text-muted">-</span>
                            @endswitch

                            @if($rule->base_source === 'tunjangan' && !empty($rule->tunjangan_items))
                                <br>
                                <small class="text-muted">
                                    ({{ implode(', ', array_map(fn($t) => ucwords(str_replace('_',' ',$t)), $rule->tunjangan_items)) }})
                                </small>
                            @endif
                        </td>

                        {{-- PENEMPATAN --}}
                        <td>
                            @if(!empty($rule->penempatan))
                                <small>
                                    {{ implode(', ', $rule->penempatan) }}
                                </small>
                            @else
                                <span class="text-muted">
                                    Tidak ditentukan
                                </span>
                            @endif
                        </td>

                        {{-- KONDISI --}}
                        <td class="text-center">
                            {{ ucfirst($rule->condition_type) }}
                            <br>
                            <small class="text-muted">
                                Trigger: {{ $rule->condition_value ?? 1 }}
                            </small>
                        </td>

                        {{-- STATUS --}}
                        <td class="text-center">
                            @if($rule->aktif)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td class="text-center">
                            <a href="{{ route('admin.potongan-gaji.edit', $rule) }}"
                               class="btn btn-sm btn-warning"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.potongan-gaji.destroy', $rule) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                        title="Hapus"
                                        onclick="return confirm('Hapus aturan ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">
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
