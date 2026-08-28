<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spmi_improvement_programs', function (Blueprint $table): void {
            $table->string('re_evaluation_status', 30)->nullable()->after('re_evaluated_at');
            $table->text('re_evaluation_error')->nullable()->after('re_evaluation_status');
            $table->timestamp('re_evaluation_requested_at')->nullable()->after('re_evaluation_error');
        });
    }

    public function down(): void
    {
        Schema::table('spmi_improvement_programs', function (Blueprint $table): void {
            $table->dropColumn(['re_evaluation_status', 're_evaluation_error', 're_evaluation_requested_at']);
        });
    }
};
