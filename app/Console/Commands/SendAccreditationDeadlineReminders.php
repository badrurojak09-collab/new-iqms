<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Accreditation;
use App\Models\User;
use App\Notifications\AccreditationDeadlineReminder;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendAccreditationDeadlineReminders extends Command
{
    protected $signature = 'accreditation:deadline-reminders {--days=30,14,7,1,0,-1}';

    protected $description = 'Kirim pengingat deadline submission akreditasi secara tenant-aware.';

    public function handle(): int
    {
        $days = collect(explode(',', (string) $this->option('days')))->map(fn (string $day): int => (int) trim($day))->unique();
        $today = CarbonImmutable::today();
        $sent = 0;

        Accreditation::query()->whereNotNull('planned_submission_date')->whereNotIn('status', ['closed', 'archived'])->chunkById(100, function ($accreditations) use ($days, $today, &$sent): void {
            foreach ($accreditations as $accreditation) {
                $remaining = $today->diffInDays($accreditation->planned_submission_date, false);
                if (! $days->contains($remaining)) {
                    continue;
                }
                $recipients = User::query()->where(function ($query) use ($accreditation): void {
                    $query->where('perguruan_tinggi_id', $accreditation->perguruan_tinggi_id)
                        ->orWhere(function ($nested) use ($accreditation): void {
                            $nested->where('yayasan_id', $accreditation->perguruanTinggi?->yayasan_id)->whereNull('perguruan_tinggi_id');
                        });
                })->where(function ($query) use ($accreditation): void {
                    $query->whereDoesntHave('programStudis')
                        ->orWhereHas('programStudis', fn ($q) => $q->whereKey($accreditation->program_studi_id));
                })->get();
                foreach ($recipients as $recipient) {
                    $key = sprintf('accreditation-deadline:%d:%s:%d:%d', $accreditation->getKey(), $today->toDateString(), $remaining, $recipient->getKey());
                    $exists = $recipient->notifications()->whereJsonContains('data->dedupe_key', $key)->exists();
                    if (! $exists) {
                        $recipient->notify(new AccreditationDeadlineReminder($accreditation, $remaining, $key));
                        $sent++;
                    }
                }
            }
        });

        $this->info("Reminder dikirim: {$sent}");

        return self::SUCCESS;
    }
}
