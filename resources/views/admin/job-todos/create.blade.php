@extends('layouts.app')

@section('title','Buat Job Todo')

@section('content')
<div class="container-fluid">

    <h1 class="mb-4">Buat Job Todo</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.job-todos.store') }}">
                @csrf

                {{-- JUDUL --}}
                <div class="form-group">
                    <label>Judul Job</label>
                    <input type="text"
                           name="title"
                           class="form-control"
                           required>
                </div>

                {{-- DESKRIPSI --}}
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3"></textarea>
                </div>

                {{-- BONUS --}}
                <div class="form-group">
                    <label>Bonus (Rp)</label>
                    <input type="number"
                           name="bonus"
                           class="form-control"
                           min="0"
                           required>
                </div>

                {{-- BROADCAST --}}
                <div class="form-group">
                    <label>Tipe Job</label>
                    <select name="broadcast"
                            class="form-control"
                            id="broadcastSelect"
                            required>
                        <option value="1">Broadcast (Semua Karyawan)</option>
                        <option value="0">Direct ke User</option>
                    </select>
                </div>

                {{-- PILIH USER --}}
                <div class="form-group d-none" id="userSelect">
                    <label>Pilih Karyawan</label>
                    <select name="users[]"
                            class="form-control"
                            multiple>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        Tekan CTRL untuk pilih lebih dari satu
                    </small>
                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Job
                </button>
                <a href="{{ route('admin.job-todos.index') }}"
                   class="btn btn-secondary">
                    Batal
                </a>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const select = document.getElementById('broadcastSelect');
    const users  = document.getElementById('userSelect');

    select.addEventListener('change', function () {
        users.classList.toggle('d-none', this.value === '1');
    });
</script>
@endpush
