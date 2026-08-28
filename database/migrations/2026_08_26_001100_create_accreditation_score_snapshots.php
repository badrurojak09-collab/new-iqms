<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_score_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_id')->constrained('accreditations')->restrictOnDelete();
            $table->foreignId('instrument_version_id')->constrained('instrument_versions')->restrictOnDelete();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 12, 4)->default(0);
            $table->string('status', 20)->default('calculated');
            $table->string('snapshot_hash', 64)->unique();
            $table->json('rule_results');
            $table->json('input_snapshot');
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['accreditation_id', 'calculated_at'], 'acc_snapshot_acc_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_score_snapshots');
    }
};
