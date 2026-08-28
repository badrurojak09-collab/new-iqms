<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_definitions', function (Blueprint $table): void {
            $table->id(); $table->string('code', 100)->unique('doc_def_code_uq'); $table->string('name'); $table->string('domain', 30); $table->string('scope_type', 30)->nullable(); $table->json('supported_formats')->nullable(); $table->text('description')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps();
        });

        Schema::create('document_template_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_definition_id')->constrained('document_definitions', 'id', 'dtv_def_fk')->cascadeOnDelete();
            $table->string('version_label', 50); $table->string('format', 10); $table->string('accreditation_body', 100)->nullable(); $table->string('instrument_version', 100)->nullable(); $table->json('schema')->nullable(); $table->char('template_hash', 64)->nullable(); $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->constrained('users', 'id', 'dtv_creator_fk')->restrictOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users', 'id', 'dtv_publisher_fk')->nullOnDelete();
            $table->timestamp('published_at')->nullable(); $table->timestamps(); $table->unique(['document_definition_id', 'version_label', 'format'], 'dtv_version_uq');
        });

        Schema::create('document_generation_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_definition_id')->constrained('document_definitions', 'id', 'dgr_def_fk')->restrictOnDelete();
            $table->foreignId('document_template_version_id')->nullable()->constrained('document_template_versions', 'id', 'dgr_template_fk')->nullOnDelete();
            $table->foreignId('perguruan_tinggi_id')->nullable()->constrained('perguruan_tinggi', 'id', 'dgr_pt_fk')->nullOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi', 'id', 'dgr_prodi_fk')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users', 'id', 'dgr_requester_fk')->restrictOnDelete();
            $table->string('period_label', 50)->nullable(); $table->json('parameters')->nullable(); $table->string('status', 20)->default('queued'); $table->text('error_message')->nullable(); $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable(); $table->timestamps();
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'status'], 'dgr_scope_status_idx');
        });

        Schema::create('document_snapshots', function (Blueprint $table): void {
            $table->id(); $table->foreignId('document_generation_request_id')->constrained('document_generation_requests', 'id', 'ds_request_fk')->cascadeOnDelete(); $table->json('payload'); $table->char('payload_hash', 64); $table->string('source_context', 100)->nullable(); $table->timestamps();
        });

        Schema::create('document_artifacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_generation_request_id')->constrained('document_generation_requests', 'id', 'da_request_fk')->cascadeOnDelete();
            $table->foreignId('document_snapshot_id')->nullable()->constrained('document_snapshots', 'id', 'da_snapshot_fk')->nullOnDelete();
            $table->string('format', 10); $table->string('file_name'); $table->string('storage_provider', 50)->default('local'); $table->string('storage_path', 500)->nullable(); $table->string('external_url', 1000)->nullable(); $table->string('mime_type', 150)->nullable(); $table->unsignedBigInteger('size_bytes')->nullable(); $table->char('sha256', 64)->nullable(); $table->string('status', 20)->default('draft'); $table->timestamps();
        });

        Schema::create('document_evidence_references', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_artifact_id')->constrained('document_artifacts', 'id', 'der_artifact_fk')->cascadeOnDelete();
            $table->foreignId('evidence_id')->constrained('evidences', 'id', 'der_evidence_fk')->restrictOnDelete();
            $table->foreignId('evidence_version_id')->nullable()->constrained('evidence_versions', 'id', 'der_version_fk')->nullOnDelete();
            $table->string('label'); $table->string('external_url', 1000)->nullable(); $table->unsignedInteger('citation_page')->nullable(); $table->string('citation_note')->nullable(); $table->timestamps();
        });

        Schema::create('document_approvals', function (Blueprint $table): void {
            $table->id(); $table->foreignId('document_artifact_id')->constrained('document_artifacts', 'id', 'dap_artifact_fk')->cascadeOnDelete(); $table->foreignId('reviewer_id')->constrained('users', 'id', 'dap_reviewer_fk')->restrictOnDelete(); $table->string('status', 20); $table->text('notes')->nullable(); $table->timestamp('reviewed_at')->nullable(); $table->timestamps(); $table->index(['document_artifact_id', 'status'], 'dap_artifact_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_approvals'); Schema::dropIfExists('document_evidence_references'); Schema::dropIfExists('document_artifacts'); Schema::dropIfExists('document_snapshots'); Schema::dropIfExists('document_generation_requests'); Schema::dropIfExists('document_template_versions'); Schema::dropIfExists('document_definitions');
    }
};
