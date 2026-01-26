@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')

<h4 class="mb-3">Detail Pengajuan</h4>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">

        {{-- KARYAWAN --}}
        <p>
            <strong>Karyawan:</strong><br>
            @if($submission->user)
                {{ $submission->user->name }}
            @else
                <span class="text-danger">User tidak ditemukan</span>
            @endif
        </p>

        {{-- JENIS --}}
        <p>
            <strong>Jenis Pengajuan:</strong><br>
            {{ $submission->nama }}
        </p>

        {{-- ALASAN --}}
        <p>
            <strong>Alasan:</strong><br>
            {{ $submission->alasan ?: '-' }}
        </p>

        {{-- STATUS --}}
        <p>
            <strong>Status:</strong><br>
            <span class="badge
                @if($submission->status === 'pending') bg-warning
                @elseif($submission->status === 'approved') bg-success
                @elseif($submission->status === 'rejected') bg-danger
                @else bg-secondary
                @endif">
                {{ strtoupper($submission->status) }}
            </span>
        </p>

        {{-- CATATAN ADMIN --}}
        @if($submission->catatan_admin)
            <p>
                <strong>Catatan Admin:</strong><br>
                {{ $submission->catatan_admin }}
            </p>
        @endif

        {{-- LAMPIRAN --}}
        @if($submission->lampiran)
            <p>
                <strong>Lampiran:</strong><br>
                <a href="{{ asset('storage/'.$submission->lampiran) }}"
                   target="_blank"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-file"></i> Lihat Lampiran
                </a>
            </p>
        @endif

    </div>
</div>

{{-- ================= ACTION ADMIN ================= --}}
@if($submission->status === 'pending')
<div class="card">
    <div class="card-body">

        {{-- APPROVE --}}
        <form method="POST"
              action="{{ route('admin.submission.approve', $submission) }}"
              class="mb-3">
            @csrf

            <div class="form-group">
                <label>Catatan Admin (Opsional)</label>
                <textarea name="catatan_admin"
                          class="form-control"
                          rows="3"
                          placeholder="Catatan admin"></textarea>
            </div>

            <button class="btn btn-success"
                    onclick="return confirm('Setujui pengajuan ini?')">
                <i class="fas fa-check"></i> Approve
            </button>
        </form>

        <hr>

        {{-- REJECT --}}
        <form method="POST"
              action="{{ route('admin.submission.reject', $submission) }}">
            @csrf

            <div class="form-group">
                <label>Alasan Penolakan</label>
                <textarea name="catatan_admin"
                          class="form-control"
                          rows="3"
                          placeholder="Alasan penolakan"
                          required></textarea>
            </div>

            <button class="btn btn-danger"
                    onclick="return confirm('Tolak pengajuan ini?')">
                <i class="fas fa-times"></i> Reject
            </button>
        </form>

    </div>
</div>
@else
<div class="mt-3">
    <form method="POST"
          action="{{ route('admin.submission.cancel', $submission) }}">
        @csrf

        <button class="btn btn-outline-secondary"
                onclick="return confirm('Kembalikan status ke pending?')">
            <i class="fas fa-undo"></i> Cancel / Reset Status
        </button>
    </form>
</div>
@endif

@endsection
