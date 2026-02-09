<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAccessController extends Controller
{
    public function index()
    {
        $admins = User::whereIn('role', User::adminRoles())
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('admin.admin-access.index', [
            'admins' => $admins,
            'roleOptions' => User::adminRoleOptions(),
            'permissionOptions' => User::adminPermissionOptions(),
        ]);
    }

    public function create()
    {
        return view('admin.admin-access.create', [
            'roleOptions' => User::adminRoleOptions(),
            'permissionOptions' => User::adminPermissionOptions(),
            'defaultPermissionsByRole' => collect(User::adminRoleOptions())
                ->mapWithKeys(fn ($label, $role) => [
                    $role => User::defaultPermissionsByRole($role),
                ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);

        if ($data['role'] === User::ROLE_OWNER && User::where('role', User::ROLE_OWNER)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'Owner sudah ada. Hanya boleh satu owner.']);
        }

        $permissions = $this->sanitizePermissions(
            $data['permissions'] ?? [],
            $data['role']
        );

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'admin_permissions' => $data['role'] === User::ROLE_OWNER ? null : $permissions,
        ]);

        return redirect()
            ->route('admin.admin-access.index')
            ->with('success', 'Admin baru berhasil dibuat');
    }

    public function edit(User $user)
    {
        $this->ensureAdminUser($user);

        return view('admin.admin-access.edit', [
            'adminUser' => $user,
            'roleOptions' => User::adminRoleOptions(),
            'permissionOptions' => User::adminPermissionOptions(),
            'defaultPermissionsByRole' => collect(User::adminRoleOptions())
                ->mapWithKeys(fn ($label, $role) => [
                    $role => User::defaultPermissionsByRole($role),
                ]),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdminUser($user);

        $data = $this->validateData($request, false, $user->id);

        if (
            $data['role'] === User::ROLE_OWNER &&
            User::where('role', User::ROLE_OWNER)->where('id', '!=', $user->id)->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'Owner sudah ada. Hanya boleh satu owner.']);
        }

        $permissions = $this->sanitizePermissions(
            $data['permissions'] ?? [],
            $data['role']
        );

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'admin_permissions' => $data['role'] === User::ROLE_OWNER ? null : $permissions,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return redirect()
            ->route('admin.admin-access.index')
            ->with('success', 'Hak akses admin berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $this->ensureAdminUser($user);

        if (auth()->id() === $user->id) {
            return back()->with('warning', 'Akun login Anda tidak bisa dihapus');
        }

        if ($user->role === User::ROLE_OWNER) {
            return back()->with('warning', 'Owner tidak bisa dihapus');
        }

        $user->delete();

        return redirect()
            ->route('admin.admin-access.index')
            ->with('success', 'Akun admin berhasil dihapus');
    }

    private function validateData(Request $request, bool $isCreate, ?int $ignoreId = null): array
    {
        $roleKeys = array_keys(User::adminRoleOptions());

        $passwordRule = $isCreate
            ? ['required', 'string', 'min:6']
            : ['nullable', 'string', 'min:6'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($ignoreId),
            ],
            'password' => $passwordRule,
            'role' => ['required', Rule::in($roleKeys)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(User::adminPermissionOptions()))],
        ]);
    }

    private function sanitizePermissions(array $permissions, string $role): array
    {
        if ($role === User::ROLE_OWNER) {
            return [];
        }

        $allowed = array_keys(User::adminPermissionOptions());
        $selected = array_values(array_intersect($permissions, $allowed));

        if (!empty($selected)) {
            return $selected;
        }

        return User::defaultPermissionsByRole($role);
    }

    private function ensureAdminUser(User $user): void
    {
        abort_if(!in_array($user->role, User::adminRoles(), true), 404);
    }
}

