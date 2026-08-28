<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

final class SortableColumnAuditTest extends TestCase
{
    public function test_count_sortable_aliases_follow_eloquent_snake_case(): void
    {
        $files = glob(app_path('Filament/Resources/**/*.php')) ?: [];
        $violations = [];
        $pattern = '~TextColumn::make\(\'([^\']+)\'.*->counts\(\'([^\']+)\'.*->sortable~';

        foreach ($files as $file) {
            foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
                if (! preg_match($pattern, $line, $match)) {
                    continue;
                }

                $column = $match[1];
                $relationship = $match[2];
                $expected = Str::snake($relationship) . '_count';

                if ($column !== $expected) {
                    $violations[] = sprintf('%s: %s should be %s', $file, $column, $expected);
                }
            }
        }

        self::assertSame([], $violations, implode(PHP_EOL, $violations));
    }

    public function test_direct_sortable_fields_do_not_use_camel_case(): void
    {
        $files = glob(app_path('Filament/Resources/**/*.php')) ?: [];
        $violations = [];
        $pattern = '~TextColumn::make\(\'([^\']+)\'.*->sortable~';

        foreach ($files as $file) {
            foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
                if (! preg_match($pattern, $line, $match)) {
                    continue;
                }

                $field = $match[1];
                if (! str_contains($field, '.') && preg_match('/[A-Z]/', $field)) {
                    $violations[] = sprintf('%s: %s', $file, $field);
                }
            }
        }

        self::assertSame([], $violations, implode(PHP_EOL, $violations));
    }
}
