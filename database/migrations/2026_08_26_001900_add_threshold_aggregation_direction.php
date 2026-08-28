<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_thresholds', function (Blueprint $table): void {
            $table->string('direction', 30)->default('auto')->after('comparison');
            $table->string('aggregation_key', 120)->nullable()->after('direction');
            $table->string('aggregation_operator', 30)->default('all')->after('aggregation_key');
            $table->unsignedInteger('aggregation_min_passed')->nullable()->after('aggregation_operator');
            $table->unsignedInteger('sequence')->default(0)->after('aggregation_min_passed');
            $table->index(['instrument_version_id', 'aggregation_key'], 'assessment_threshold_aggregation_idx');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_thresholds', function (Blueprint $table): void {
            $table->dropIndex('assessment_threshold_aggregation_idx');
            $table->dropColumn(['direction', 'aggregation_key', 'aggregation_operator', 'aggregation_min_passed', 'sequence']);
        });
    }
};
