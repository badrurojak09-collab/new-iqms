<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spmi_frameworks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->string('version_label', 50)->default('1.0');
            $table->string('status', 20)->default('draft');
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code', 'version_label'], 'spmi_framework_code_version_unique');
        });

        Schema::create('spmi_standards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_framework_id')->constrained('spmi_frameworks')->restrictOnDelete();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->text('statement');
            $table->text('basis')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['spmi_framework_id', 'code'], 'spmi_standard_framework_code_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'status'], 'spmi_standard_scope_status_idx');
        });

        Schema::create('spmi_indicators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_standard_id')->constrained('spmi_standards')->restrictOnDelete();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->string('code', 100);
            $table->string('name');
            $table->text('definition')->nullable();
            $table->string('measurement_type', 30)->default('numeric');
            $table->string('unit', 50)->nullable();
            $table->decimal('weight', 8, 4)->nullable();
            $table->string('status', 20)->default('active');
            $table->json('validation_rules')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['spmi_standard_id', 'code'], 'spmi_indicator_standard_code_unique');
            $table->index(['perguruan_tinggi_id', 'status'], 'spmi_indicator_scope_status_idx');
        });

        Schema::create('spmi_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_indicator_id')->constrained('spmi_indicators')->restrictOnDelete();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->string('period_code', 30)->nullable();
            $table->decimal('target_numeric', 18, 6)->nullable();
            $table->text('target_text')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['spmi_indicator_id', 'program_studi_id', 'period_year', 'period_code'], 'spmi_target_period_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'period_year'], 'spmi_target_scope_period_idx');
        });

        Schema::create('spmi_realizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_target_id')->constrained('spmi_targets')->restrictOnDelete();
            $table->foreignId('spmi_indicator_id')->constrained('spmi_indicators')->restrictOnDelete();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->decimal('realization_numeric', 18, 6)->nullable();
            $table->text('realization_text')->nullable();
            $table->string('source_type', 50)->nullable();
            $table->string('source_reference', 255)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->unique(['spmi_indicator_id', 'program_studi_id', 'period_year'], 'spmi_realization_period_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'period_year', 'status'], 'spmi_realization_scope_period_idx');
        });

        Schema::create('spmi_evaluations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_realization_id')->constrained('spmi_realizations')->restrictOnDelete();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('result', 30)->default('not_met');
            $table->decimal('achievement_percentage', 8, 4)->nullable();
            $table->text('analysis')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'status'], 'spmi_evaluation_scope_status_idx');
        });

        Schema::create('spmi_improvement_programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_evaluation_id')->constrained('spmi_evaluations')->restrictOnDelete();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('code', 80);
            $table->string('title');
            $table->text('action_plan');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('status', 20)->default('planned');
            $table->text('completion_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code'], 'spmi_improvement_scope_code_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'status', 'due_date'], 'spmi_improvement_scope_status_due_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spmi_improvement_programs');
        Schema::dropIfExists('spmi_evaluations');
        Schema::dropIfExists('spmi_realizations');
        Schema::dropIfExists('spmi_targets');
        Schema::dropIfExists('spmi_indicators');
        Schema::dropIfExists('spmi_standards');
        Schema::dropIfExists('spmi_frameworks');
    }
};
