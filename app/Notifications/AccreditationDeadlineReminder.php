<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Accreditation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class AccreditationDeadlineReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Accreditation $accreditation, public readonly int $daysRemaining, public readonly string $dedupeKey) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $urgency = $this->daysRemaining < 0 ? 'overdue' : ($this->daysRemaining <= 7 ? 'urgent' : 'upcoming');

        return [
            'dedupe_key' => $this->dedupeKey,
            'type' => 'accreditation_deadline',
            'title' => $this->daysRemaining < 0 ? 'Deadline akreditasi terlewat' : 'Deadline akreditasi mendekat',
            'body' => $this->accreditation->title.' — '.abs($this->daysRemaining).' hari '.($this->daysRemaining < 0 ? 'terlambat' : 'tersisa'),
            'accreditation_id' => $this->accreditation->getKey(),
            'perguruan_tinggi_id' => $this->accreditation->perguruan_tinggi_id,
            'program_studi_id' => $this->accreditation->program_studi_id,
            'deadline' => $this->accreditation->planned_submission_date?->toDateString(),
            'days_remaining' => $this->daysRemaining,
            'urgency' => $urgency,
            'action_url' => Route::has('filament.admin.resources.accreditations.edit') && $this->accreditation->getKey() !== null
                ? route('filament.admin.resources.accreditations.edit', ['record' => $this->accreditation])
                : url('/admin'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Pengingat deadline akreditasi')->line($this->accreditation->title);
    }
}
