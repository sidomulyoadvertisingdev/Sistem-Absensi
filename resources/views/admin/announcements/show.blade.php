@extends('layouts.app')

@section('title', 'Detail Pengumuman')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Detail Pengumuman</h4>
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-1">{{ $announcement->title }}</h5>
        <small class="text-muted">
            {{ $announcement->published_at?->format('d M Y H:i') }}
        </small>

        @if($announcement->image_url)
            <div class="mt-3">
                <img src="{{ $announcement->image_url }}" alt="Gambar Pengumuman" class="img-fluid rounded">
            </div>
        @endif

        <div class="mt-3">
            {!! nl2br(e($announcement->content)) !!}
        </div>
    </div>
</div>
@endsection
