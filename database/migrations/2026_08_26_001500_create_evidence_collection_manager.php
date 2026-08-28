<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('accreditation_id')->nullable()->constrained('accreditations')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('code', 100);
            $table->string('name', 255);
            $table->string('provider', 50)->default('google_drive');
            $table->text('root_folder_url')->nullable();
            $table->string('root_folder_id', 255)->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('description')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code'], 'evidence_collections_pt_code_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'status'], 'evidence_collections_scope_status_idx');
        });

        Schema::create('evidence_collection_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evidence_collection_id')->constrained('evidence_collections')->cascadeOnDelete();
            $table->foreignId('evidence_id')->nullable()->constrained('evidences')->nullOnDelete();
            $table->string('requirement_code', 150);
            $table->string('requirement_title', 500);
            $table->string('target_type', 50)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('status', 30)->default('missing');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['evidence_collection_id', 'requirement_code'], 'evidence_collection_items_requirement_unique');
            $table->index(['target_type', 'target_id'], 'evidence_collection_items_target_idx');
            $table->index(['evidence_collection_id', 'status'], 'evidence_collection_items_status_idx');
        });

        Schema::create('evidence_link_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidences')->cascadeOnDelete();
            $table->foreignId('evidence_version_id')->nullable()->constrained('evidence_versions')->nullOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('not_checked');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->char('url_hash', 64);
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            $table->index(['evidence_id', 'status'], 'evidence_link_checks_evidence_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_link_checks');
        Schema::dropIfExists('evidence_collection_items');
        Schema::dropIfExists('evidence_collections');
    }
};
