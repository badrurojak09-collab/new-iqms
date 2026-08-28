<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migration_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('run_uuid')->unique();
            $table->string('source_name', 100);
            $table->string('source_checksum', 64)->nullable();
            $table->string('mode', 20)->default('dry_run');
            $table->string('status', 20)->default('running');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('migrated_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('exception_rows')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('migration_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('migration_run_id')->constrained('migration_runs')->cascadeOnDelete();
            $table->string('source_table', 100);
            $table->string('source_pk', 100);
            $table->string('target_table', 100);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('source_fingerprint', 64);
            $table->string('status', 20)->default('migrated');
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['source_table', 'source_pk', 'target_table'], 'migration_ledger_source_target_unique');
            $table->index(['migration_run_id', 'status'], 'migration_ledger_run_status_idx');
        });

        Schema::create('migration_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('migration_run_id')->constrained('migration_runs')->cascadeOnDelete();
            $table->string('source_table', 100);
            $table->string('source_pk', 100);
            $table->string('reason_code', 50);
            $table->text('reason');
            $table->string('payload_fingerprint', 64);
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['source_table', 'status'], 'migration_exception_source_status_idx');
        });

        Schema::create('dual_run_controls', function (Blueprint $table): void {
            $table->id();
            $table->string('domain', 50);
            $table->string('mode', 20)->default('legacy_read');
            $table->boolean('writes_enabled')->default(false);
            $table->boolean('comparison_enabled')->default(true);
            $table->timestamp('cutover_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rollback_plan')->nullable();
            $table->timestamps();
            $table->unique('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dual_run_controls');
        Schema::dropIfExists('migration_exceptions');
        Schema::dropIfExists('migration_ledgers');
        Schema::dropIfExists('migration_runs');
    }
};
