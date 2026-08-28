<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_bodies', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('kind', 30)->default('external');
            $table->string('website')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('instrument_families', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_body_id')->nullable()->constrained('accreditation_bodies')->nullOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->string('scope_type', 30);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['accreditation_body_id', 'code']);
        });

        Schema::create('instrument_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_family_id')->constrained('instrument_families')->restrictOnDelete();
            $table->foreignId('parent_version_id')->nullable()->constrained('instrument_versions')->nullOnDelete();
            $table->string('version_label', 50);
            $table->string('status', 20)->default('draft');
            $table->string('source_reference')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->json('changelog')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['instrument_family_id', 'version_label']);
            $table->index(['status', 'effective_from']);
        });

        Schema::create('instrument_nodes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('instrument_nodes')->cascadeOnDelete();
            $table->string('node_type', 30);
            $table->string('code', 100);
            $table->string('title');
            $table->text('requirement')->nullable();
            $table->text('guidance')->nullable();
            $table->decimal('weight', 8, 4)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code']);
            $table->index(['instrument_version_id', 'parent_id', 'sort_order'], 'instrument_nodes_version_parent_order_idx');
        });

        Schema::create('instrument_scoring_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('rule_type', 30);
            $table->json('expression');
            $table->json('parameters')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code']);
        });

        Schema::create('led_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('validation_rules')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code']);
        });

        Schema::create('led_template_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('led_template_id')->constrained('led_templates')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->string('code', 100);
            $table->string('title');
            $table->text('guidance')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('validation_rules')->nullable();
            $table->timestamps();
            $table->unique(['led_template_id', 'code']);
        });

        Schema::create('lkps_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('row_definition')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code']);
        });

        Schema::create('lkps_template_columns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lkps_template_id')->constrained('lkps_templates')->cascadeOnDelete();
            $table->string('column_key', 100);
            $table->string('label');
            $table->string('data_type', 30);
            $table->string('unit', 50)->nullable();
            $table->boolean('is_required')->default(false);
            $table->decimal('min_value', 18, 6)->nullable();
            $table->decimal('max_value', 18, 6)->nullable();
            $table->unsignedTinyInteger('decimal_scale')->nullable();
            $table->json('allowed_values')->nullable();
            $table->string('source_type', 50)->nullable();
            $table->json('formula')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['lkps_template_id', 'column_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lkps_template_columns');
        Schema::dropIfExists('lkps_templates');
        Schema::dropIfExists('led_template_sections');
        Schema::dropIfExists('led_templates');
        Schema::dropIfExists('instrument_scoring_rules');
        Schema::dropIfExists('instrument_nodes');
        Schema::dropIfExists('instrument_versions');
        Schema::dropIfExists('instrument_families');
        Schema::dropIfExists('accreditation_bodies');
    }
};
