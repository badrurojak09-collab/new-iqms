<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('readiness_mapping_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('readiness_result_id')->constrained('readiness_results')->cascadeOnDelete();
            $table->foreignId('instrument_mapping_id')->constrained('instrument_mappings')->restrictOnDelete();
            $table->foreignId('source_indicator_id')->nullable()->constrained('assessment_indicators')->nullOnDelete();
            $table->decimal('coverage_weight', 10, 4)->nullable();
            $table->decimal('source_completion_percent', 7, 4)->default(0);
            $table->decimal('source_evidence_percent', 7, 4)->default(0);
            $table->string('status', 30)->default('not_ready');
            $table->text('gap_reason')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->unique(['readiness_result_id', 'instrument_mapping_id'], 'readiness_mapping_result_unique');
            $table->index(['instrument_mapping_id', 'status'], 'readiness_mapping_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readiness_mapping_results');
    }
};
