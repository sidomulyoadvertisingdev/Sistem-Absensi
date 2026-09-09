@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')

<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Pengumuman</h4>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Buat Pengumuman
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

            <form method="POST"
                  action="{{ route('admin.announcements.destroy', $a) }}"
                  class="d-inline"
                  onsubmit="return confirm('Hapus pengumuman \"{{ $a->title }}\"?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@empty
<p class="text-muted">Belum ada pengumuman</p>
@endforelse

<div class="mt-3">
    {{ $announcements->links() }}
</div>
</div>

@endsection
