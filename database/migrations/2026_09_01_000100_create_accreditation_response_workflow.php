<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accreditation_responses', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->after('last_edited_by')->constrained('users', 'id', 'ar_reviewed_by_fk')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
            $table->foreignId('approved_by')->nullable()->after('review_notes')->constrained('users', 'id', 'ar_approved_by_fk')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('locked_by')->nullable()->after('approved_at')->constrained('users', 'id', 'ar_locked_by_fk')->nullOnDelete();
            $table->timestamp('locked_at')->nullable()->after('locked_by');
            $table->unsignedInteger('revision_no')->default(1)->after('locked_at');
            $table->index(['accreditation_id', 'status'], 'ar_accreditation_status_idx');
            $table->index(['status', 'locked_at'], 'ar_status_locked_idx');
        });

        Schema::create('accreditation_response_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accreditation_response_id')->constrained('accreditation_responses', 'id', 'arr_response_fk')->cascadeOnDelete();
            $table->unsignedInteger('revision_no');
            $table->string('status', 30);
            $table->text('response_text')->nullable();
            $table->decimal('response_numeric', 18, 6)->nullable();
            $table->json('response_json')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users', 'id', 'arr_changed_by_fk')->nullOnDelete();
            $table->text('change_reason')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
            $table->unique(['accreditation_response_id', 'revision_no'], 'arr_response_revision_unique');
            $table->index(['accreditation_response_id', 'status'], 'arr_response_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_response_revisions');

        Schema::table('accreditation_responses', function (Blueprint $table): void {
            $table->dropIndex('ar_accreditation_status_idx');
            $table->dropIndex('ar_status_locked_idx');
            $table->dropForeign('ar_reviewed_by_fk');
            $table->dropForeign('ar_approved_by_fk');
            $table->dropForeign('ar_locked_by_fk');
            $table->dropColumn([
                'reviewed_by', 'reviewed_at', 'review_notes',
                'approved_by', 'approved_at', 'locked_by', 'locked_at', 'revision_no',
            ]);
        });
    }
};
