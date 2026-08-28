<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_criteria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->constrained('instrument_nodes')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name', 500);
            $table->decimal('weight', 10, 4)->nullable();
            $table->decimal('minimum_score', 10, 4)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code'], 'assessment_criteria_version_code_unique');
            $table->index(['instrument_version_id', 'sort_order'], 'assessment_criteria_version_order_idx');
        });

        Schema::create('assessment_elements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_criterion_id')->constrained('assessment_criteria')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->constrained('instrument_nodes')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('title', 500);
            $table->string('element_type', 30)->default('mixed');
            $table->decimal('weight', 10, 4)->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['assessment_criterion_id', 'code'], 'assessment_elements_criterion_code_unique');
        });

        Schema::create('assessment_indicators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_element_id')->constrained('assessment_elements')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name', 500);
            $table->string('unit', 100)->nullable();
            $table->string('direction', 30)->default('higher_is_better');
            $table->string('data_type', 30)->default('decimal');
            $table->json('target_definition')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['assessment_element_id', 'code'], 'assessment_indicators_element_code_unique');
        });

        Schema::create('assessment_scales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name', 255);
            $table->string('scale_type', 30)->default('numeric');
            $table->decimal('min_value', 10, 4)->nullable();
            $table->decimal('max_value', 10, 4)->nullable();
            $table->unsignedTinyInteger('precision')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code'], 'assessment_scales_version_code_unique');
        });

        Schema::create('assessment_scale_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_scale_id')->constrained('assessment_scales')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('label', 255);
            $table->decimal('numeric_value', 10, 4)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['assessment_scale_id', 'code'], 'assessment_scale_options_scale_code_unique');
        });

        Schema::create('assessment_rubrics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->foreignId('assessment_scale_option_id')->nullable()->constrained('assessment_scale_options')->nullOnDelete();
            $table->decimal('min_score', 10, 4)->nullable();
            $table->decimal('max_score', 10, 4)->nullable();
            $table->string('label', 255);
            $table->longText('description');
            $table->text('evidence_expectation')->nullable();
            $table->timestamps();
            $table->index(['instrument_version_id', 'instrument_node_id'], 'assessment_rubrics_version_node_idx');
        });

        Schema::create('scoring_rule_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_scoring_rule_id')->constrained('instrument_scoring_rules')->cascadeOnDelete();
            $table->foreignId('source_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->foreignId('source_indicator_id')->nullable()->constrained('assessment_indicators')->nullOnDelete();
            $table->string('source_response_key', 150)->nullable();
            $table->decimal('weight', 10, 4)->nullable();
            $table->decimal('coefficient', 10, 4)->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['instrument_scoring_rule_id', 'sort_order'], 'scoring_rule_items_rule_order_idx');
        });

        Schema::create('scoring_rule_dependencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('scoring_rule_id')->constrained('instrument_scoring_rules')->cascadeOnDelete();
            $table->foreignId('depends_on_rule_id')->constrained('instrument_scoring_rules')->cascadeOnDelete();
            $table->string('dependency_type', 30)->default('required');
            $table->timestamps();
            $table->unique(['scoring_rule_id', 'depends_on_rule_id'], 'scoring_rule_dependencies_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_rule_dependencies');
        Schema::dropIfExists('scoring_rule_items');
        Schema::dropIfExists('assessment_rubrics');
        Schema::dropIfExists('assessment_scale_options');
        Schema::dropIfExists('assessment_scales');
        Schema::dropIfExists('assessment_indicators');
        Schema::dropIfExists('assessment_elements');
        Schema::dropIfExists('assessment_criteria');
    }
};
