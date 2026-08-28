<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Ui\StatusLabel;
use Tests\TestCase;

final class IndonesianUiTest extends TestCase
{
    public function test_application_uses_indonesian_locale_and_jakarta_timezone(): void
    {
        self::assertSame('id', config('app.locale'));
        self::assertSame('Asia/Jakarta', config('app.timezone'));
    }

    public function test_workflow_statuses_have_indonesian_labels(): void
    {
        self::assertSame('Direncanakan', StatusLabel::for('planned'));
        self::assertSame('Diajukan', StatusLabel::for('submitted'));
        self::assertSame('Disetujui', StatusLabel::for('approved'));
        self::assertSame('Gagal', StatusLabel::for('failed'));
    }
}
