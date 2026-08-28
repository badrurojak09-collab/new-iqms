<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spmi_improvement_programs', function (Blueprint $table): void {
            $table->foreignId('spmi_indicator_id')->nullable()->after('spmi_evaluation_id')->constrained('spmi_indicators')->nullOnDelete();
            $table->foreignId('spmi_target_id')->nullable()->after('spmi_indicator_id')->constrained('spmi_targets')->nullOnDelete();
            $table->foreignId('accreditation_id')->nullable()->after('program_studi_id')->constrained('accreditations')->nullOnDelete();
            $table->foreignId('re_evaluated_readiness_run_id')->nullable()->after('verified_at')->constrained('readiness_runs')->nullOnDelete();
            $table->timestamp('re_evaluated_at')->nullable()->after('re_evaluated_readiness_run_id');
            $table->index(['spmi_indicator_id', 'spmi_target_id'], 'spmi_improvement_indicator_target_idx');
            $table->index(['accreditation_id', 'status'], 'spmi_improvement_accreditation_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('spmi_improvement_programs', function (Blueprint $table): void {
            $table->dropIndex('spmi_improvement_indicator_target_idx');
            $table->dropIndex('spmi_improvement_accreditation_status_idx');
            $table->dropForeign(['spmi_indicator_id']);
            $table->dropForeign(['spmi_target_id']);
            $table->dropForeign(['accreditation_id']);
            $table->dropForeign(['re_evaluated_readiness_run_id']);
            $table->dropColumn(['spmi_indicator_id', 'spmi_target_id', 'accreditation_id', 're_evaluated_readiness_run_id', 're_evaluated_at']);
        });
    }
};
