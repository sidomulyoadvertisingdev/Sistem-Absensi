@extends('layouts.app')

@section('title', 'Job Todo')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-0">Job Todo</h1>
            <small class="text-muted">Manajemen job untuk karyawan</small>
        </div>

        <a href="{{ route('admin.job-todos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat Job Todo
        </a>
    </div>

    {{-- ================= ALERT ================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead class="thead-light">
                    <tr class="text-center">
                        <th>Judul</th>
                        <th width="120">Bonus</th>
                        <th width="120">Tipe</th>
                        <th width="120">Penerima</th>
                        <th width="140">Status</th>
                        <th width="120">Dibuat</th>
                        <th width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($todos as $todo)
                    <tr>
                        {{-- JUDUL --}}
                        <td>
                            <strong>{{ $todo->title }}</strong>
                        </td>

                        {{-- BONUS --}}
                        <td class="text-right">
                            Rp {{ number_format($todo->bonus, 0, ',', '.') }}
                        </td>

                        {{-- TIPE --}}
                        <td class="text-center">
                            @if($todo->broadcast)
                                <span class="badge badge-info">
                                    <i class="fas fa-bullhorn"></i> Broadcast
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    <i class="fas fa-user"></i> Direct
                                </span>
                            @endif
                        </td>

                        {{-- TOTAL USER --}}
                        <td class="text-center">
                            {{ $todo->users_count }} orang
                        </td>

                        {{-- STATUS --}}
                        <td class="text-center">
                            @switch($todo->status)
                                @case('open')
                                    <span class="badge badge-success">
                                        <i class="fas fa-unlock"></i> Open
                                    </span>
                                    @break

                                @case('in_progress')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-spinner fa-spin"></i> In Progress
                                    </span>
                                    @break

                                @case('done')
                                    <span class="badge badge-primary">
                                        <i class="fas fa-check-circle"></i> Done
                                    </span>
                                    @break

                                @case('closed')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-lock"></i> Closed
                                    </span>
                                    @break
                            @endswitch
                        </td>

                        {{-- CREATED --}}
                        <td class="text-center">
                            {{ $todo->created_at->format('d M Y') }}
                        </td>

                        {{-- AKSI --}}
                        <td class="text-center">
                            <a href="{{ route('admin.job-todos.show', $todo->id) }}"
                               class="btn btn-info btn-sm"
                               title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>

                            @if(in_array($todo->status, ['open', 'in_progress']))
                                <form action="{{ route('admin.job-todos.close', $todo->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Tutup job ini?')">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-danger btn-sm" title="Tutup Job">
                                        <i class="fas fa-lock"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                            Belum ada job todo
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= PAGINATION ================= --}}
        @if ($todos->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $todos->firstItem() }} - {{ $todos->lastItem() }}
                    dari {{ $todos->total() }} data
                </small>

                <div>
                    {{ $todos->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
