<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_collections', function (Blueprint $table): void {
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('locked_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable()->after('locked_by');
            $table->text('lock_reason')->nullable()->after('locked_at');
            $table->timestamp('submitted_at')->nullable()->after('lock_reason');
        });

        Schema::create('readiness_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('run_type', 30)->default('readiness');
            $table->string('status', 30)->default('running');
            $table->string('engine_version', 50)->default('readiness-v1');
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('ready_items')->default(0);
            $table->decimal('completion_percent', 7, 4)->default(0);
            $table->decimal('weighted_score', 12, 6)->default(0);
            $table->char('input_hash', 64)->nullable();
            $table->json('summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['accreditation_id', 'created_at'], 'readiness_runs_accreditation_date_idx');
        });

        Schema::create('readiness_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('readiness_run_id')->constrained('readiness_runs')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->foreignId('assessment_element_id')->nullable()->constrained('assessment_elements')->nullOnDelete();
            $table->string('item_key', 180);
            $table->string('status', 30)->default('not_ready');
            $table->decimal('weight', 10, 4)->nullable();
            $table->decimal('completion_percent', 7, 4)->default(0);
            $table->decimal('evidence_percent', 7, 4)->default(0);
            $table->decimal('score', 12, 6)->nullable();
            $table->unsignedInteger('gap_count')->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
            $table->unique(['readiness_run_id', 'item_key'], 'readiness_results_run_item_unique');
            $table->index(['readiness_run_id', 'status'], 'readiness_results_run_status_idx');
        });

        Schema::create('readiness_gaps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('readiness_run_id')->constrained('readiness_runs')->cascadeOnDelete();
            $table->foreignId('readiness_result_id')->nullable()->constrained('readiness_results')->nullOnDelete();
            $table->string('gap_type', 40);
            $table->string('severity', 20)->default('medium');
            $table->string('item_key', 180);
            $table->text('description');
            $table->string('resolution_status', 30)->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['readiness_run_id', 'resolution_status'], 'readiness_gaps_run_resolution_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readiness_gaps');
        Schema::dropIfExists('readiness_results');
        Schema::dropIfExists('readiness_runs');
        Schema::table('evidence_collections', function (Blueprint $table): void {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'locked_by', 'locked_at', 'lock_reason', 'submitted_at']);
        });
    }
};
