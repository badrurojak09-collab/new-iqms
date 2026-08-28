<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('scope_type', 30);
            $table->string('title');
            $table->string('status', 30)->default('readiness');
            $table->date('planned_submission_date')->nullable();
            $table->date('submitted_at')->nullable();
            $table->date('decision_date')->nullable();
            $table->string('decision_result', 50)->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code'], 'accreditation_pt_code_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'scope_type', 'status'], 'accreditation_scope_status_idx');
        });

        Schema::create('accreditation_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->string('code', 100);
            $table->string('title');
            $table->string('section_type', 30)->default('led');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->decimal('readiness_percent', 8, 4)->default(0);
            $table->timestamps();
            $table->unique(['accreditation_id', 'code'], 'accreditation_section_code_unique');
        });

        Schema::create('accreditation_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->foreignId('accreditation_section_id')->constrained('accreditation_sections')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->string('response_key', 120);
            $table->string('response_type', 30)->default('text');
            $table->longText('response_text')->nullable();
            $table->decimal('response_numeric', 18, 6)->nullable();
            $table->json('response_json')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['accreditation_id', 'response_key'], 'accreditation_response_key_unique');
            $table->index(['accreditation_section_id', 'status'], 'accreditation_response_section_status_idx');
        });

        Schema::create('accreditation_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->unsignedInteger('submission_no');
            $table->string('package_hash', 64)->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['accreditation_id', 'submission_no'], 'accreditation_submission_no_unique');
        });

        Schema::create('accreditation_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->foreignId('accreditation_response_id')->nullable()->constrained('accreditation_responses')->nullOnDelete();
            $table->foreignId('assessor_id')->constrained('users')->restrictOnDelete();
            $table->string('assessment_type', 30)->default('internal_review');
            $table->string('result', 30)->nullable();
            $table->decimal('score', 10, 4)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
            $table->index(['accreditation_id', 'assessment_type', 'status'], 'accreditation_assessment_status_idx');
        });

        Schema::create('accreditation_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->string('decision_type', 30)->default('internal');
            $table->string('result', 50);
            $table->text('notes')->nullable();
            $table->date('decision_date');
            $table->date('valid_until')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('accreditation_readiness_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->string('item_type', 30);
            $table->string('item_key', 120);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['accreditation_id', 'item_type', 'item_key'], 'accreditation_readiness_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_readiness_items');
        Schema::dropIfExists('accreditation_decisions');
        Schema::dropIfExists('accreditation_assessments');
        Schema::dropIfExists('accreditation_submissions');
        Schema::dropIfExists('accreditation_responses');
        Schema::dropIfExists('accreditation_sections');
        Schema::dropIfExists('accreditations');
    }
};
