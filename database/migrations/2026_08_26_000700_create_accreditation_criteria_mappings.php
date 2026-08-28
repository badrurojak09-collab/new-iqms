<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_criteria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->restrictOnDelete();
            $table->string('code', 100);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'code']);
            $table->index(['instrument_version_id', 'sort_order']);
        });

        Schema::create('instrument_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->restrictOnDelete();
            $table->foreignId('instrument_node_id')->constrained('instrument_nodes')->cascadeOnDelete();
            $table->foreignId('accreditation_criterion_id')->constrained('accreditation_criteria')->cascadeOnDelete();
            $table->string('mapping_type', 30)->default('primary');
            $table->string('target_type', 30)->default('led');
            $table->string('target_key', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['instrument_version_id', 'instrument_node_id', 'accreditation_criterion_id', 'target_type'], 'instrument_mapping_unique');
            $table->index(['accreditation_criterion_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_mappings');
        Schema::dropIfExists('accreditation_criteria');
    }
};
