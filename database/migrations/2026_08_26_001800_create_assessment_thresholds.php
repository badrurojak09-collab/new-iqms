<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_thresholds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->foreignId('assessment_element_id')->nullable()->constrained('assessment_elements')->cascadeOnDelete();
            $table->foreignId('assessment_indicator_id')->nullable()->constrained('assessment_indicators')->cascadeOnDelete();
            $table->foreignId('assessment_scale_id')->nullable()->constrained('assessment_scales')->nullOnDelete();
            $table->foreignId('assessment_rubric_id')->nullable()->constrained('assessment_rubrics')->nullOnDelete();
            $table->string('code', 120);
            $table->string('name', 255);
            $table->string('comparison', 30)->default('gte');
            $table->decimal('target_value', 20, 6)->nullable();
            $table->decimal('min_value', 20, 6)->nullable();
            $table->decimal('max_value', 20, 6)->nullable();
            $table->decimal('pass_score', 12, 6)->default(100);
            $table->decimal('fail_score', 12, 6)->default(0);
            $table->decimal('minimum_score', 12, 6)->nullable();
            $table->decimal('weight', 10, 4)->default(1);
            $table->string('status', 20)->default('draft');
            $table->json('config')->nullable();
            $table->string('source_reference', 500)->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code'], 'assessment_threshold_version_code_unique');
            $table->index(['instrument_version_id', 'status'], 'assessment_threshold_version_status_idx');
            $table->index(['assessment_element_id', 'assessment_indicator_id'], 'assessment_threshold_element_indicator_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_thresholds');
    }
};
