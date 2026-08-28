<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\ReevaluateAccreditationReadiness;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class ReevaluateAccreditationReadinessJobTest extends TestCase
{
    public function test_readiness_job_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        ReevaluateAccreditationReadiness::dispatch(15, 7);

        Queue::assertPushed(ReevaluateAccreditationReadiness::class, function (ReevaluateAccreditationReadiness $job): bool {
            return $job->programId === 15 && $job->actorId === 7;
        });
    }
}
