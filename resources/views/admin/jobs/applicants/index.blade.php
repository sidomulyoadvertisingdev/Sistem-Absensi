@extends('layouts.app')

@section('title','Data Pelamar')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">
            @isset($job)
                Pelamar – {{ $job->title }}
            @else
                Semua Pelamar Pekerjaan
            @endisset
        </h1>

        @isset($job)
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Job
            </a>
        @endisset
    </div>

    {{-- FILTER & SEARCH --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Cari nama atau email..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                            <option value="review" {{ request('status')=='review'?'selected':'' }}>Review</option>
                            <option value="interview" {{ request('status')=='interview'?'selected':'' }}>Interview</option>
                            <option value="training" {{ request('status')=='training'?'selected':'' }}>Training</option>
                            <option value="accepted" {{ request('status')=='accepted'?'selected':'' }}>Diterima</option>
                            <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Ditolak</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ url()->current() }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Lowongan</th>
                        <th>Status</th>
                        <th>Tanggal Lamar</th>
                        <th width="260" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($applicants as $applicant)
                    <tr>
                        <td>{{ $applicant->name }}</td>
                        <td>{{ $applicant->email }}</td>
                        <td>{{ $applicant->job->title ?? '-' }}</td>
                        <td>
                            @switch($applicant->status)
                                @case('accepted')
                                    <span class="badge badge-success">Diterima</span>
                                    @break
                                @case('rejected')
                                    <span class="badge badge-danger">Ditolak</span>
                                    @break
                                @case('review')
                                    <span class="badge badge-info">Review</span>
                                    @break
                                @case('interview')
                                    <span class="badge badge-primary">Interview</span>
                                    @break
                                @case('training')
                                    <span class="badge badge-warning">Training</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">Pending</span>
                            @endswitch
                        </td>
                        <td>{{ $applicant->created_at->format('d-m-Y') }}</td>
                        <td class="text-center">

                            {{-- DOWNLOAD FILE --}}
                            @if(is_array($applicant->answers))
                                @foreach($applicant->answers as $key => $value)
                                    @if(\Illuminate\Support\Str::startsWith($value, 'job-applicants'))
                                        <a href="{{ route('admin.jobs.applicants.download', [$applicant, $key]) }}"
                                           class="btn btn-info btn-sm mb-1"
                                           title="Download {{ ucfirst($key) }}">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                @endforeach
                            @endif

                            {{-- REVIEW --}}
                            <form action="{{ route('admin.jobs.applicants.updateStatus', $applicant) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="review">
                                <button class="btn btn-secondary btn-sm" title="Review">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>

                            {{-- INTERVIEW --}}
                            <form action="{{ route('admin.jobs.applicants.updateStatus', $applicant) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="interview">
                                <button class="btn btn-primary btn-sm" title="Interview">
                                    <i class="fas fa-comments"></i>
                                </button>
                            </form>

                            {{-- TRAINING --}}
                            <form action="{{ route('admin.jobs.applicants.updateStatus', $applicant) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="training">
                                <button class="btn btn-warning btn-sm" title="Training">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </button>
                            </form>

                            {{-- TERIMA --}}
                            <form action="{{ route('admin.jobs.applicants.updateStatus', $applicant) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="accepted">
                                <button class="btn btn-success btn-sm"
                                        onclick="return confirm('Terima pelamar ini?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>

                            {{-- TOLAK --}}
                            <form action="{{ route('admin.jobs.applicants.updateStatus', $applicant) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Tolak pelamar ini?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Data pelamar tidak ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $applicants->links() }}
    </div>

</div>
@endsection
