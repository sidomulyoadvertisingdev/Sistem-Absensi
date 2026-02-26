<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubmissionStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Submission $submission)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->submission->status;

        $statusLabel = match ($status) {
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            default => $status,
        };

        return [
            'category' => 'submission',
            'title' => 'Status Pengajuan Diperbarui',
            'message' => "Pengajuan {$this->submission->nama} Anda {$statusLabel}.",
            'submission_id' => $this->submission->id,
            'submission_type_id' => $this->submission->submission_type_id,
            'submission_type_name' => $this->submission->nama,
            'status' => $status,
            'catatan_admin' => $this->submission->catatan_admin,
            'approved_at' => $this->submission->approved_at?->toIso8601String(),
            'rejected_at' => $this->submission->rejected_at?->toIso8601String(),
        ];
    }
}
