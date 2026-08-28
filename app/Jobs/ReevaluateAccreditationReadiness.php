<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Accreditation\ReadinessScoringService;
use App\Models\AuditLog;
use App\Models\SpmiImprovementProgram;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReevaluateAccreditationReadiness implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public readonly int $programId, public readonly int $actorId) {}

    public function backoff(): array
    {
        return [30, 120, 600];
    }

    public function handle(ReadinessScoringService $readiness): void
    {
        $program = SpmiImprovementProgram::query()->with('accreditation')->findOrFail($this->programId);
        if ($program->status !== 'verified' || $program->accreditation_id === null) {
            return;
        }
        $actor = User::query()->findOrFail($this->actorId);
        $program->update(['re_evaluation_status' => 'running', 're_evaluation_error' => null]);
        $run = $readiness->calculate($actor, $program->accreditation);
        $program->update(['re_evaluated_readiness_run_id' => $run->getKey(), 're_evaluated_at' => now(), 're_evaluation_status' => 'completed', 're_evaluation_error' => null]);
        $this->audit($actor, $program, 'spmi.improvement.readiness_re_evaluated', ['readiness_run_id' => $run->getKey(), 'accreditation_id' => $program->accreditation_id, 'queue' => true]);
    }

    public function failed(Throwable $exception): void
    {
        $program = SpmiImprovementProgram::query()->find($this->programId);
        if ($program) {
            $program->update(['re_evaluation_status' => 'failed', 're_evaluation_error' => mb_substr($exception->getMessage(), 0, 5000)]);
        }
        Log::error('Automatic readiness re-evaluation failed.', ['program_id' => $this->programId, 'exception' => $exception]);
    }

    private function audit(User $actor, object $record, string $event, array $context): void
    {
        AuditLog::create(['user_id' => $actor->getKey(), 'event' => $event, 'auditable_type' => $record::class, 'auditable_id' => $record->getKey(), 'route' => null, 'ip_address' => null, 'user_agent' => 'queue', 'context' => $context]);
    }
}
