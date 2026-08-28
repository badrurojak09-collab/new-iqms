<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_thresholds', function (Blueprint $table): void {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });

        Schema::table('assessment_rubrics', function (Blueprint $table): void {
            $table->string('status', 20)->default('draft')->after('evidence_expectation');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            $table->index(['instrument_version_id', 'status'], 'assessment_rubric_version_status_idx');
        });

        Schema::table('rtl_actions', function (Blueprint $table): void {
            $table->foreignId('readiness_gap_id')->nullable()->after('rtm_decision_id')->constrained('readiness_gaps')->nullOnDelete();
            $table->index(['readiness_gap_id', 'status'], 'rtl_action_gap_status_idx');
        });

        Schema::table('rtm_decisions', function (Blueprint $table): void {
            $table->foreignId('readiness_gap_id')->nullable()->after('ami_finding_id')->constrained('readiness_gaps')->nullOnDelete();
            $table->index(['readiness_gap_id', 'status'], 'rtm_decision_gap_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_thresholds', function (Blueprint $table): void {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'approval_notes']);
        });
        Schema::table('rtm_decisions', function (Blueprint $table): void {
            $table->dropIndex('rtm_decision_gap_status_idx');
            $table->dropForeign(['readiness_gap_id']);
            $table->dropColumn('readiness_gap_id');
        });
        Schema::table('rtl_actions', function (Blueprint $table): void {
            $table->dropIndex('rtl_action_gap_status_idx');
            $table->dropForeign(['readiness_gap_id']);
            $table->dropColumn('readiness_gap_id');
        });
        Schema::table('assessment_rubrics', function (Blueprint $table): void {
            $table->dropIndex('assessment_rubric_version_status_idx');
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'approval_notes']);
        });
    }
};
