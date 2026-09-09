<div class="list-group list-group-flush">
    @forelse($users as $user)
        <label class="list-group-item d-flex align-items-center">
            <input type="checkbox" name="member_ids[]" value="{{ $user->id }}" class="mr-2">
            <span class="flex-grow-1">
                <strong>{{ $user->name }}</strong>
                <small class="text-muted d-block">
                    {{ ['owner' => 'Pemilik', 'admin' => 'Admin', 'karyawan' => 'Karyawan', 'user' => 'Pengguna'][$user->role] ?? ucfirst($user->role) }}
                </small>
            </span>
        </label>
    @empty
        <div class="list-group-item text-muted">Tidak ada pengguna.</div>
    @endforelse
</div>
