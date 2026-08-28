<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('disk', 50)->default('local');
            $table->string('storage_path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('visibility', 20)->default('private');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['disk', 'storage_path'], 'documents_disk_path_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id']);
            $table->index('sha256');
        });

        Schema::create('evidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code']);
            $table->index(['status', 'valid_until']);
        });

        Schema::create('evidence_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidences')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version_no');
            $table->string('change_reason')->nullable();
            $table->char('manifest_hash', 64)->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['evidence_id', 'version_no']);
            $table->unique(['evidence_id', 'document_id']);
        });

        Schema::create('evidence_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidences')->cascadeOnDelete();
            $table->string('linkable_type', 100);
            $table->unsignedBigInteger('linkable_id');
            $table->string('relation_type', 50)->default('supports');
            $table->unsignedInteger('citation_page')->nullable();
            $table->string('citation_note')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();
            $table->unique(['evidence_id', 'linkable_type', 'linkable_id', 'relation_type'], 'evidence_link_unique');
            $table->index(['linkable_type', 'linkable_id']);
        });

        Schema::create('evidence_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidences')->cascadeOnDelete();
            $table->foreignId('evidence_version_id')->nullable()->constrained('evidence_versions')->nullOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['evidence_id', 'status']);
        });

        Schema::create('evidence_integrity_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->char('expected_sha256', 64);
            $table->char('actual_sha256', 64)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['document_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_integrity_checks');
        Schema::dropIfExists('evidence_reviews');
        Schema::dropIfExists('evidence_links');
        Schema::dropIfExists('evidence_versions');
        Schema::dropIfExists('evidences');
        Schema::dropIfExists('documents');
    }
};
