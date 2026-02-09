@php
    $isEdit = isset($adminUser);
    $selectedPermissions = old(
        'permissions',
        $isEdit ? ($adminUser->resolvedAdminPermissions() ?? []) : []
    );

    if (!is_array($selectedPermissions)) {
        $selectedPermissions = [];
    }
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nama</label>
            <input type="text"
                name="name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $adminUser->name ?? '') }}"
                required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Email</label>
            <input type="email"
                name="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $adminUser->email ?? '') }}"
                required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ $isEdit ? 'Password Baru (opsional)' : 'Password' }}</label>
            <input type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                {{ $isEdit ? '' : 'required' }}>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Role Admin</label>
            <select name="role"
                id="roleSelect"
                class="form-control @error('role') is-invalid @enderror"
                required>
                @foreach($roleOptions as $roleValue => $roleLabel)
                    <option value="{{ $roleValue }}"
                        {{ old('role', $adminUser->role ?? '') === $roleValue ? 'selected' : '' }}>
                        {{ $roleLabel }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0 font-weight-bold">Hak Akses Menu</h6>
        <small class="text-muted">Pilih menu yang boleh diakses akun ini.</small>
    </div>
    <div class="card-body">
        <div id="ownerInfo" class="alert alert-info d-none mb-3">
            Role Owner otomatis memiliki akses penuh ke semua menu.
        </div>

        <div class="row">
            @foreach($permissionOptions as $permissionKey => $permissionLabel)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input permission-checkbox"
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permissionKey }}"
                            id="perm_{{ $permissionKey }}"
                            {{ in_array($permissionKey, $selectedPermissions, true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="perm_{{ $permissionKey }}">
                            {{ $permissionLabel }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        @error('permissions')
            <div class="text-danger mt-2">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const roleSelect = document.getElementById('roleSelect');
        const ownerInfo = document.getElementById('ownerInfo');
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const defaultByRole = @json($defaultPermissionsByRole);

        function applyRoleState() {
            const currentRole = roleSelect.value;
            const isOwner = currentRole === 'owner';
            const defaults = defaultByRole[currentRole] || [];

            ownerInfo.classList.toggle('d-none', !isOwner);

            permissionCheckboxes.forEach((cb) => {
                if (isOwner) {
                    cb.checked = true;
                    cb.disabled = true;
                    return;
                }

                cb.disabled = false;
            });

            if (!isOwner) {
                const hasChecked = Array.from(permissionCheckboxes).some((cb) => cb.checked);
                if (!hasChecked) {
                    permissionCheckboxes.forEach((cb) => {
                        cb.checked = defaults.includes(cb.value);
                    });
                }
            }
        }

        roleSelect.addEventListener('change', applyRoleState);
        applyRoleState();
    })();
</script>
@endpush

