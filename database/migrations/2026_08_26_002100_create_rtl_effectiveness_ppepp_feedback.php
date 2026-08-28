<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtl_effectiveness_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rtl_action_id')->constrained('rtl_actions')->cascadeOnDelete();
            $table->foreignId('spmi_evaluation_id')->nullable()->constrained('spmi_evaluations')->nullOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->string('outcome', 30)->default('pending');
            $table->unsignedTinyInteger('effectiveness_score')->nullable();
            $table->text('observed_result')->nullable();
            $table->text('evidence_summary')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('ppepp_stage', 30)->default('evaluation');
            $table->boolean('follow_up_required')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['rtl_action_id', 'status'], 'rtl_eff_action_status_idx');
            $table->index(['spmi_evaluation_id', 'outcome'], 'rtl_eff_spmi_outcome_idx');
        });

        Schema::table('spmi_improvement_programs', function (Blueprint $table): void {
            $table->foreignId('effectiveness_review_id')->nullable()->after('spmi_evaluation_id')->constrained('rtl_effectiveness_reviews')->nullOnDelete();
            $table->index(['effectiveness_review_id', 'status'], 'spmi_improvement_effectiveness_idx');
        });
    }

    public function down(): void
    {
        Schema::table('spmi_improvement_programs', function (Blueprint $table): void {
            $table->dropIndex('spmi_improvement_effectiveness_idx');
            $table->dropForeign(['effectiveness_review_id']);
            $table->dropColumn('effectiveness_review_id');
        });
        Schema::dropIfExists('rtl_effectiveness_reviews');
    }
};
