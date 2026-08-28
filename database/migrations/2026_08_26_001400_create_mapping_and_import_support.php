<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instrument_mappings', function (Blueprint $table): void {
            $table->foreignId('source_indicator_id')->nullable()->after('instrument_node_id')->constrained('assessment_indicators')->nullOnDelete();
            $table->foreignId('target_element_id')->nullable()->after('accreditation_criterion_id')->constrained('assessment_elements')->nullOnDelete();
            $table->string('source_type', 40)->default('instrument_node')->after('mapping_type');
            $table->decimal('coverage_weight', 10, 4)->nullable()->after('target_key');
            $table->boolean('is_required')->default(false)->after('coverage_weight');
            $table->string('approval_status', 20)->default('draft')->after('is_required');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('source_reference', 500)->nullable()->after('approved_at');
            $table->index(['source_indicator_id', 'approval_status'], 'instrument_map_source_status_idx');
            $table->index(['target_element_id', 'approval_status'], 'instrument_map_target_status_idx');
        });

        Schema::create('instrument_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_family_id')->constrained('instrument_families')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('original_name', 255);
            $table->string('format', 10);
            $table->char('source_hash', 64)->nullable();
            $table->string('status', 20)->default('preview');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->json('summary')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();
            $table->index(['instrument_family_id', 'status'], 'instrument_import_family_status_idx');
        });

        Schema::create('instrument_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instrument_import_batch_id')->constrained('instrument_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('entity_type', 30);
            $table->string('entity_code', 150)->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('valid');
            $table->json('errors')->nullable();
            $table->timestamps();
            $table->unique(['instrument_import_batch_id', 'row_number'], 'instrument_import_rows_batch_row_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_import_rows');
        Schema::dropIfExists('instrument_import_batches');
        Schema::table('instrument_mappings', function (Blueprint $table): void {
            $table->dropIndex('instrument_map_source_status_idx');
            $table->dropIndex('instrument_map_target_status_idx');
            $table->dropForeign(['source_indicator_id']);
            $table->dropForeign(['target_element_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['source_indicator_id', 'target_element_id', 'source_type', 'coverage_weight', 'is_required', 'approval_status', 'approved_by', 'approved_at', 'source_reference']);
        });
    }
};
