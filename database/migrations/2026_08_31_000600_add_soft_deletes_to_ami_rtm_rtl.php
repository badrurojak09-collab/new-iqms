<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['ami_cycles', 'ami_assignments', 'ami_checklist_items', 'ami_findings', 'rtm_meetings', 'rtm_participants', 'rtm_decisions', 'rtl_effectiveness_reviews'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void { $table->softDeletes(); });
            }
        }
    }

    public function down(): void
    {
        foreach (['ami_cycles', 'ami_assignments', 'ami_checklist_items', 'ami_findings', 'rtm_meetings', 'rtm_participants', 'rtm_decisions', 'rtl_effectiveness_reviews'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table): void { $table->dropSoftDeletes(); });
            }
        }
    }
};
