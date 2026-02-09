@extends('layouts.app')

@section('title', 'Edit Hak Akses Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 font-weight-bold">Edit Hak Akses Admin</h4>
            <small class="text-muted">Atur role dan menu yang dapat diakses oleh {{ $adminUser->name }}.</small>
        </div>
        <a href="{{ route('admin.admin-access.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <form action="{{ route('admin.admin-access.update', $adminUser->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.admin-access._form')
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Update Hak Akses
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

