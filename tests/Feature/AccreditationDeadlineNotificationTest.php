<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Accreditation;
use App\Models\User;
use App\Notifications\AccreditationDeadlineReminder;
use Carbon\CarbonImmutable;
use Tests\TestCase;

final class AccreditationDeadlineNotificationTest extends TestCase
{
    public function test_notification_payload_contains_tenant_and_idempotency_metadata(): void
    {
        $accreditation = new Accreditation([
            'id' => 42,
            'perguruan_tinggi_id' => 7,
            'program_studi_id' => 9,
            'title' => 'Akreditasi Prodi Informatika',
            'planned_submission_date' => CarbonImmutable::today()->addDays(7),
        ]);
        $notification = new AccreditationDeadlineReminder($accreditation, 7, 'deadline:42:7:9');
        $payload = $notification->toDatabase(new User);

        self::assertSame('deadline:42:7:9', $payload['dedupe_key']);
        self::assertSame(7, $payload['perguruan_tinggi_id']);
        self::assertSame(9, $payload['program_studi_id']);
        self::assertSame('urgent', $payload['urgency']);
        self::assertSame(7, $payload['days_remaining']);
    }
}
