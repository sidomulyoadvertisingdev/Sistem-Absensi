@extends('layouts.app')

@section('title', 'Tambah Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 font-weight-bold">Tambah Admin Baru</h4>
            <small class="text-muted">Owner dapat menentukan role dan hak akses menu.</small>
        </div>
        <a href="{{ route('admin.admin-access.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <form action="{{ route('admin.admin-access.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @include('admin.admin-access._form')
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

