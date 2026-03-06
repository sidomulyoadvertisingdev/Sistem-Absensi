<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isPanelAdmin()) {
            abort(403, 'Akses khusus panel admin');
        }

        $routeName = $request->route()?->getName() ?? '';
        $permission = $this->resolvePermissionByRoute($routeName);

        if ($permission && !$user->hasAdminPermission($permission)) {
            abort(403, 'Anda tidak memiliki hak akses untuk menu ini');
        }

        return $next($request);
    }

    private function resolvePermissionByRoute(string $routeName): ?string
    {
        $map = [
            'admin.dashboard' => 'dashboard',

            'admin.users*' => 'users',
            'admin.admin-access*' => 'manage_admin_access',
            'admin.integration-tokens*' => 'integrations',

            'admin.karyawan*' => 'karyawan',
            'admin.absensi*' => 'absensi',
            'admin.lembur*' => 'lembur',
            'admin.jadwal*' => 'jadwal',
            'admin.gaji*' => 'gaji',
            'admin.laporan*' => 'laporan',

            'admin.salary-deduction-rules*' => 'potongan',
            'admin.potongan-gaji*' => 'potongan',

            'admin.jobs*' => 'jobs',
            'admin.job-todos*' => 'job_todos',
            'admin.pelanggaran*' => 'pelanggaran',

            'admin.submission-types*' => 'submission_types',
            'admin.submission*' => 'submission',
            'admin.announcements*' => 'announcements',
        ];

        foreach ($map as $pattern => $permission) {
            if (Str::is($pattern, $routeName)) {
                return $permission;
            }
        }

        return null;
    }
}
