<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lkps_datasets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->cascadeOnDelete();
            $table->foreignId('lkps_template_id')->constrained('lkps_templates')->cascadeOnDelete();
            $table->string('status', 30)->default('draft');
            $table->json('rows_data')->nullable();
            $table->json('summary_metrics')->nullable();
            $table->json('validation_errors')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['accreditation_id', 'lkps_template_id'], 'lkps_dataset_acc_tpl_unique');
            $table->index(['accreditation_id', 'status'], 'lkps_dataset_acc_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lkps_datasets');
    }
};
