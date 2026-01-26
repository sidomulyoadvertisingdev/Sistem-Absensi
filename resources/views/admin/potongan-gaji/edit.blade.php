@extends('layouts.app')

@section('title','Edit Aturan Potongan Gaji')

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Aturan Potongan Gaji</h5>
        </div>

        <form method="POST" action="{{ route('admin.potongan-gaji.update', $rule) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.potongan-gaji._form', ['rule' => $rule])
            </div>

            <div class="card-footer text-right">
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="{{ route('admin.potongan-gaji.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
