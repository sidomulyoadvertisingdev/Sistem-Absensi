@extends('layouts.app')

@section('title', 'Detail Job Todo')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Detail Job Todo</h1>

        <a href="{{ route('admin.job-todos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- INFO JOB --}}
    <div class="card mb-4">
        <div class="card-body">
            <h4>{{ $jobTodo->title }}</h4>

            <p class="text-muted mb-2">
                {{ $jobTodo->description ?? '-' }}
            </p>

            <p>
                <strong>Bonus:</strong>
                Rp {{ number_format($jobTodo->bonus) }}
            </p>

            <p>
                <strong>Tipe:</strong>
                @if($jobTodo->broadcast)
                    <span class="badge badge-info">Broadcast</span>
                @else
                    <span class="badge badge-primary">Direct Assign</span>
                @endif
            </p>

            <p>
                <strong>Status Job:</strong>
                @if($jobTodo->status === 'open')
                    <span class="badge badge-success">Open</span>
                @else
                    <span class="badge badge-secondary">Closed</span>
                @endif
            </p>

            @if($jobTodo->status === 'open')
                <form action="{{ route('admin.job-todos.close', $jobTodo->id) }}"
                      method="POST"
                      onsubmit="return confirm('Tutup job ini?')">
                    @csrf
                    @method('PUT')
                    <button class="btn btn-danger btn-sm">
                        <i class="fas fa-lock"></i> Tutup Job
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- LIST USER --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Karyawan yang Menerima Job</h3>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Bonus</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($jobTodo->users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>
                            @php $status = $user->pivot->status; @endphp

                            @if($status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($status === 'accepted')
                                <span class="badge badge-info">Dikerjakan</span>
                            @elseif($status === 'completed')
                                <span class="badge badge-success">Selesai</span>
                            @endif
                        </td>
                        <td>
                            @if($status === 'completed')
                                Rp {{ number_format($jobTodo->bonus) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            Belum ada karyawan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
