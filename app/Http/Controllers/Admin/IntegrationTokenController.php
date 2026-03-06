<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class IntegrationTokenController extends Controller
{
    public function docs(): View
    {
        $baseUrl = $this->integrationBaseUrl();

        return view('admin.integration-tokens.docs', [
            'baseUrl' => $baseUrl,
            'abilityOptions' => $this->abilityOptions(),
            'endpointDocs' => $this->endpointDocs($baseUrl),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $tokens = PersonalAccessToken::query()
            ->with('tokenable')
            ->where('tokenable_type', User::class)
            ->where('name', 'like', 'integration:%')
            ->when(
                !$user->isOwner(),
                fn ($query) => $query->where('tokenable_id', $user->id)
            )
            ->latest()
            ->get()
            ->filter(fn (PersonalAccessToken $token) => $token->tokenable instanceof User
                && $token->tokenable->isPanelAdmin())
            ->values();

        return view('admin.integration-tokens.index', [
            'tokens' => $tokens,
            'abilityOptions' => $this->abilityOptions(),
            'expiryOptions' => $this->expiryOptions(),
            'baseUrl' => $this->integrationBaseUrl(),
            'generatedToken' => session('generated_token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string'],
            'expires_in_days' => ['nullable', 'integer'],
        ]);

        $allowedAbilities = array_keys($this->abilityOptions());
        $abilities = array_values(array_intersect($data['abilities'], $allowedAbilities));

        if (empty($abilities)) {
            return back()
                ->withInput()
                ->withErrors([
                    'abilities' => 'Pilih minimal satu izin akses API.',
                ]);
        }

        if (in_array('integration.attendance.payroll.write', $abilities, true)
            && !in_array('integration.attendance.payroll.read', $abilities, true)) {
            $abilities[] = 'integration.attendance.payroll.read';
        }

        $expiryOptions = $this->expiryOptions();
        $expiresInDays = (int) ($data['expires_in_days'] ?? 90);

        if (!array_key_exists($expiresInDays, $expiryOptions)) {
            return back()
                ->withInput()
                ->withErrors([
                    'expires_in_days' => 'Pilihan masa berlaku token tidak valid.',
                ]);
        }

        $expiresAt = $expiresInDays > 0 ? now()->addDays($expiresInDays) : null;
        $tokenName = 'integration:' . trim($data['name']);

        $plainTextToken = $request->user()
            ->createToken($tokenName, $abilities, $expiresAt)
            ->plainTextToken;

        return redirect()
            ->route('admin.integration-tokens.index')
            ->with('success', 'Token integrasi berhasil dibuat.')
            ->with('generated_token', [
                'name' => trim($data['name']),
                'plain_text_token' => $plainTextToken,
                'abilities' => $abilities,
                'expires_at' => $expiresAt?->toDateTimeString(),
            ]);
    }

    public function destroy(Request $request, PersonalAccessToken $token): RedirectResponse
    {
        abort_if(!$this->canManageToken($request->user(), $token), 403, 'Anda tidak dapat menghapus token ini.');

        $token->delete();

        return redirect()
            ->route('admin.integration-tokens.index')
            ->with('success', 'Token integrasi berhasil dihapus.');
    }

    private function canManageToken(User $user, PersonalAccessToken $token): bool
    {
        if (!str_starts_with((string) $token->name, 'integration:')) {
            return false;
        }

        if ($token->tokenable_type !== User::class) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return (int) $token->tokenable_id === (int) $user->id;
    }

    private function abilityOptions(): array
    {
        return [
            'integration.attendance.report.read' => 'Baca laporan absensi',
            'integration.attendance.payroll.read' => 'Baca data payroll',
            'integration.attendance.payroll.write' => 'Ubah payroll dan proses bayar',
        ];
    }

    private function expiryOptions(): array
    {
        return [
            30 => '30 hari',
            90 => '90 hari',
            180 => '180 hari',
            365 => '365 hari',
            0 => 'Tidak kedaluwarsa',
        ];
    }

    private function integrationBaseUrl(): string
    {
        return 'https://admin.sidomulyoproject.com';
    }

    private function endpointDocs(string $baseUrl): array
    {
        return [
            [
                'title' => 'Laporan Absensi',
                'method' => 'GET',
                'url' => $baseUrl . '/api/integrations/attendance/report',
                'ability' => 'integration.attendance.report.read',
                'description' => 'Mengambil rekap absensi, keterlambatan, lembur, dan estimasi payroll per karyawan.',
                'params' => [
                    ['name' => 'date_from', 'type' => 'date', 'required' => false, 'description' => 'Tanggal awal, format YYYY-MM-DD.'],
                    ['name' => 'date_to', 'type' => 'date', 'required' => false, 'description' => 'Tanggal akhir, format YYYY-MM-DD.'],
                    ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Filter nama, email, NIK, jabatan, atau penempatan.'],
                    ['name' => 'penempatan', 'type' => 'string', 'required' => false, 'description' => 'Filter outlet/penempatan.'],
                ],
                'curl' => <<<CURL
curl --request GET "{$baseUrl}/api/integrations/attendance/report?date_from=2026-03-01&date_to=2026-03-31&penempatan=Outlet%20A" \
  --header "Authorization: Bearer {TOKEN}" \
  --header "Accept: application/json"
CURL,
                'response' => <<<'JSON'
{
  "status": "ok",
  "message": "Laporan absensi berhasil diambil.",
  "data": {
    "period": {
      "date_from": "2026-03-01",
      "date_to": "2026-03-31"
    },
    "summary": {
      "employee_count": 15,
      "present_total": 320,
      "absent_total": 12,
      "late_total": 28,
      "overtime_minutes_total": 540,
      "payroll_total": 45800000
    },
    "rows": [
      {
        "employee_id": 7,
        "employee_name": "Budi Santoso",
        "outlet_name": "Outlet A",
        "position_name": "Kasir",
        "working_days": 26,
        "present": 24,
        "leave": 1,
        "sick": 0,
        "absent": 1,
        "late_count": 3,
        "overtime_minutes": 120,
        "payroll_amount": 3200000
      }
    ]
  }
}
JSON,
            ],
            [
                'title' => 'Daftar Payroll Karyawan',
                'method' => 'GET',
                'url' => $baseUrl . '/api/integrations/attendance/salaries',
                'ability' => 'integration.attendance.payroll.read',
                'description' => 'Mengambil master salary dan hasil kalkulasi payroll per periode.',
                'params' => [
                    ['name' => 'month', 'type' => 'integer', 'required' => false, 'description' => 'Bulan payroll.'],
                    ['name' => 'year', 'type' => 'integer', 'required' => false, 'description' => 'Tahun payroll.'],
                    ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Filter nama, email, NIK, jabatan, atau penempatan.'],
                    ['name' => 'penempatan', 'type' => 'string', 'required' => false, 'description' => 'Filter outlet/penempatan.'],
                ],
                'curl' => <<<CURL
curl --request GET "{$baseUrl}/api/integrations/attendance/salaries?month=3&year=2026" \
  --header "Authorization: Bearer {TOKEN}" \
  --header "Accept: application/json"
CURL,
                'response' => <<<'JSON'
{
  "status": "ok",
  "message": "Data gaji berhasil diambil.",
  "data": {
    "period": {
      "month": 3,
      "year": 2026,
      "ym": "2026-03"
    },
    "summary": {
      "employee_count": 15,
      "salary_active_count": 15,
      "paid_count": 7,
      "payroll_total": 45800000
    },
    "rows": [
      {
        "employee_id": 7,
        "employee_name": "Budi Santoso",
        "salary_active": true,
        "is_paid": false,
        "payroll_period": "2026-03",
        "gaji_diterima": 3200000
      }
    ]
  }
}
JSON,
            ],
            [
                'title' => 'Update Master Payroll',
                'method' => 'PUT',
                'url' => $baseUrl . '/api/integrations/attendance/salaries/{user}',
                'ability' => 'integration.attendance.payroll.write',
                'description' => 'Menyimpan master gaji dan pengaturan payroll untuk satu karyawan.',
                'params' => [
                    ['name' => 'gaji_pokok', 'type' => 'numeric', 'required' => true, 'description' => 'Nilai gaji pokok.'],
                    ['name' => 'gaji_harian', 'type' => 'numeric', 'required' => false, 'description' => 'Diisi jika mode harian manual.'],
                    ['name' => 'gaji_harian_mode', 'type' => 'enum', 'required' => false, 'description' => 'manual, pokok, atau pokok_plus_tunjangan.'],
                    ['name' => 'tunjangan_umum', 'type' => 'numeric', 'required' => false, 'description' => 'Nilai tunjangan umum.'],
                    ['name' => 'tunjangan_transport', 'type' => 'numeric', 'required' => false, 'description' => 'Nilai tunjangan transport.'],
                    ['name' => 'tunjangan_thr', 'type' => 'numeric', 'required' => false, 'description' => 'Nilai tunjangan THR.'],
                    ['name' => 'tunjangan_kesehatan', 'type' => 'numeric', 'required' => false, 'description' => 'Nilai tunjangan kesehatan.'],
                    ['name' => 'lembur_per_jam', 'type' => 'numeric', 'required' => false, 'description' => 'Tarif lembur per jam.'],
                    ['name' => 'include_tunjangan', 'type' => 'boolean', 'required' => false, 'description' => 'Apakah tunjangan masuk payroll bulanan.'],
                    ['name' => 'aktif', 'type' => 'boolean', 'required' => false, 'description' => 'Status master gaji aktif/tidak.'],
                ],
                'curl' => <<<CURL
curl --request PUT "{$baseUrl}/api/integrations/attendance/salaries/7" \
  --header "Authorization: Bearer {TOKEN}" \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data "{
    \"gaji_pokok\": 3000000,
    \"gaji_harian_mode\": \"pokok_plus_tunjangan\",
    \"tunjangan_umum\": 250000,
    \"tunjangan_transport\": 150000,
    \"lembur_per_jam\": 25000,
    \"include_tunjangan\": true,
    \"aktif\": true
  }"
CURL,
                'response' => <<<'JSON'
{
  "status": "ok",
  "message": "Master gaji berhasil disimpan.",
  "data": {
    "employee_id": 7,
    "employee_name": "Budi Santoso",
    "salary": {
      "gaji_pokok": 3000000,
      "gaji_harian_mode": "pokok_plus_tunjangan",
      "include_tunjangan": true,
      "aktif": true
    }
  }
}
JSON,
            ],
            [
                'title' => 'Proses Pembayaran Payroll',
                'method' => 'POST',
                'url' => $baseUrl . '/api/integrations/attendance/salaries/{user}/pay',
                'ability' => 'integration.attendance.payroll.write',
                'description' => 'Menandai payroll periode tertentu sebagai sudah dibayar dan mengunci absensi periode itu.',
                'params' => [
                    ['name' => 'period', 'type' => 'string', 'required' => false, 'description' => 'Format YYYY-MM.'],
                    ['name' => 'month', 'type' => 'integer', 'required' => false, 'description' => 'Alternatif jika period tidak dipakai.'],
                    ['name' => 'year', 'type' => 'integer', 'required' => false, 'description' => 'Alternatif jika period tidak dipakai.'],
                ],
                'curl' => <<<CURL
curl --request POST "{$baseUrl}/api/integrations/attendance/salaries/7/pay" \
  --header "Authorization: Bearer {TOKEN}" \
  --header "Accept: application/json" \
  --header "Content-Type: application/json" \
  --data "{
    \"period\": \"2026-03\"
  }"
CURL,
                'response' => <<<'JSON'
{
  "status": "ok",
  "message": "Pembayaran gaji berhasil diproses.",
  "data": {
    "employee_id": 7,
    "period": "2026-03",
    "paid_at": "2026-03-06 15:10:00"
  }
}
JSON,
            ],
        ];
    }
}
