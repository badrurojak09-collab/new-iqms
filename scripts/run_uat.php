<?php

declare(strict_types=1);

use App\Models\Accreditation;
use App\Models\Evidence;
use App\Models\InstrumentVersion;
use App\Models\SpmiEvaluation;
use App\Models\SpmiFramework;
use App\Models\SpmiImprovementProgram;
use App\Models\SpmiIndicator;
use App\Models\SpmiRealization;
use App\Models\SpmiStandard;
use App\Models\SpmiTarget;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Artisan::call('migrate:fresh', ['--force' => true]);
Artisan::call('db:seed', ['--force' => true]);

$checks = [
    'Organisasi Yayasan' => \App\Models\Yayasan::query()->where('kode', 'YYS-DEMO')->exists(),
    'Perguruan Tinggi' => \App\Models\PerguruanTinggi::query()->where('kode_pt', 'PT-DEMO')->exists(),
    'Program Studi' => \App\Models\ProgramStudi::query()->where('kode_prodi', 'TI-DEMO')->exists(),
    'SPMI Framework' => SpmiFramework::query()->where('code', 'SPMI-DEMO')->exists(),
    'SPMI Standard' => SpmiStandard::query()->where('code', 'STD-01')->exists(),
    'SPMI Indicator' => SpmiIndicator::query()->where('code', 'IND-01')->exists(),
    'SPMI Target' => SpmiTarget::query()->where('period_code', 'TA-2026')->exists(),
    'SPMI Realization Verified' => SpmiRealization::query()->where('status', 'verified')->exists(),
    'SPMI Evaluation' => SpmiEvaluation::query()->where('result', 'partially_met')->exists(),
    'SPMI Improvement Program' => SpmiImprovementProgram::query()->where('code', 'RTL-SPMI-001')->exists(),
    'Instrument Version Published' => InstrumentVersion::query()->where('version_label', 'Demo 1.0')->where('status', 'published')->exists(),
    'Evidence Cloud' => Evidence::query()->where('code', 'EVD-DEMO-001')->where('status', 'accepted')->exists(),
    'Accreditation Institution' => Accreditation::query()->where('code', 'AKR-DEMO-2026')->where('scope_type', 'institution')->exists(),
];

foreach ($checks as $label => $passed) {
    echo sprintf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $label);
}

$failed = array_filter($checks, static fn (bool $passed): bool => ! $passed);
echo sprintf("UAT dummy checks: %d passed, %d failed\n", count($checks) - count($failed), count($failed));
exit($failed === [] ? 0 : 1);
