@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')

<div class="d-flex justify-content-between mb-3">
    <h4>Pengumuman</h4>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
        + Buat Pengumuman
    </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@forelse($announcements as $a)
<div class="card mb-2">
    <div class="card-body">
        <h5>{{ $a->title }}</h5>
        <small class="text-muted">
            {{ $a->published_at }}
        </small>

        <div class="mt-2">
            <a href="{{ route('admin.announcements.show', $a) }}"
               class="btn btn-sm btn-info">
                Detail
            </a>

            <form method="POST"
                  action="{{ route('admin.announcements.toggle', $a) }}"
                  class="d-inline">
                @csrf
                <button class="btn btn-sm btn-secondary">
                    {{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
        </div>
    </div>
</div>
@empty
<p class="text-muted">Belum ada pengumuman</p>
@endforelse

@endsection
