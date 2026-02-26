<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Submission;
use App\Notifications\SubmissionStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    public function index()
    {
        $submissions = Submission::with([
            'user:id,name',
            'type:id,nama,is_izin_pulang_awal',
        ])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.submission.index', compact('submissions'));
    }

    public function show(Submission $submission)
    {
        $submission->load([
            'user:id,name',
            'type:id,nama,is_izin_pulang_awal',
        ]);

        return view('admin.submission.show', compact('submission'));
    }

    public function approve(Request $request, Submission $submission)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string',
        ]);

        if ($submission->status !== 'pending') {
            return redirect()
                ->route('admin.submission.show', $submission)
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $submission->loadMissing(['user', 'type']);
        $approvedAt = now();

        DB::transaction(function () use ($submission, $request, $approvedAt) {
            $submission->update([
                'status' => 'approved',
                'catatan_admin' => $request->catatan_admin,
                'approved_by' => auth()->id(),
                'approved_at' => $approvedAt,
                'rejected_by' => null,
                'rejected_at' => null,
            ]);

            if ($submission->type?->is_izin_pulang_awal) {
                $this->closeAttendanceOnApproval($submission, $approvedAt);
            }

            if ($submission->user) {
                $submission->user->notify(
                    new SubmissionStatusNotification($submission->fresh())
                );
            }
        });

        return redirect()
            ->route('admin.submission.show', $submission)
            ->with('success', 'Pengajuan berhasil disetujui');
    }

    public function reject(Request $request, Submission $submission)
    {
        $request->validate([
            'catatan_admin' => 'required|string',
        ]);

        if ($submission->status !== 'pending') {
            return redirect()
                ->route('admin.submission.show', $submission)
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $submission->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $submission->loadMissing('user');
        if ($submission->user) {
            $submission->user->notify(
                new SubmissionStatusNotification($submission->fresh())
            );
        }

        return redirect()
            ->route('admin.submission.show', $submission)
            ->with('success', 'Pengajuan berhasil ditolak');
    }

    public function cancel(Submission $submission)
    {
        $submission->update([
            'status' => 'pending',
            'catatan_admin' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
        ]);

        return redirect()
            ->route('admin.submission.show', $submission)
            ->with('success', 'Status berhasil dikembalikan ke pending');
    }

    private function closeAttendanceOnApproval(Submission $submission, Carbon $approvedAt): void
    {
        $user = $submission->user;

        if (!$user) {
            return;
        }

        $tanggal = $approvedAt->toDateString();

        $absensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$absensi || $absensi->locked || !$absensi->jam_masuk || !empty($absensi->jam_pulang)) {
            return;
        }

        $absensi->jam_pulang = $approvedAt->format('H:i:s');

        $jadwal = $user->resolveWorkSchedule($tanggal);
        $menitTerlambat = 0;
        $status = 'hadir';

        if ($jadwal) {
            $absensiJamMasuk = $this->normalizeJamInput($absensi->jam_masuk);
            $absensiJamPulang = $this->normalizeJamInput($absensi->jam_pulang);
            $jadwalJamMasuk = $this->normalizeJamInput($jadwal->jam_masuk ?? null);
            $jadwalJamPulang = $this->normalizeJamInput($jadwal->jam_pulang ?? null);

            if ($absensiJamMasuk && $jadwalJamMasuk) {
                $jamMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $absensiJamMasuk);
                $batasMasuk = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $jadwalJamMasuk);

                if (!empty($jadwal->toleransi_masuk)) {
                    $batasMasuk->addMinutes((int) $jadwal->toleransi_masuk);
                }

                if ($jamMasuk->gt($batasMasuk)) {
                    $menitTerlambat += $batasMasuk->diffInMinutes($jamMasuk);
                }
            }

            if ($absensiJamPulang && $jadwalJamPulang) {
                $jamPulang = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $absensiJamPulang);
                $batasPulang = Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' ' . $jadwalJamPulang);

                if ($jamPulang->lt($batasPulang)) {
                    $menitTerlambat += $jamPulang->diffInMinutes($batasPulang);
                }
            }

            $status = $menitTerlambat > 0 ? 'terlambat' : 'hadir';
        }

        $absensi->menit_terlambat = $menitTerlambat;
        $absensi->status = $status;
        $absensi->locked = true;
        $absensi->save();
    }

    private function normalizeJamInput(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $jamInput = trim((string) $value);
        if ($jamInput === '') {
            return null;
        }

        $jamNormalized = preg_replace(
            '/^(\d{4}-\d{2}-\d{2})\s+\1\s+/',
            '$1 ',
            str_replace('.', ':', $jamInput)
        );

        try {
            return Carbon::parse($jamNormalized)->format('H:i:s');
        } catch (\Exception $e) {
            if (preg_match('/\b(\d{2}:\d{2}(?::\d{2})?)\b/', $jamNormalized, $matches)) {
                $format = strlen($matches[1]) === 5 ? 'H:i' : 'H:i:s';
                return Carbon::createFromFormat($format, $matches[1])->format('H:i:s');
            }
        }

        return null;
    }
}
