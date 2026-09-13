<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perguruan_tinggi_standards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi', 'id', 'pts_std_pt_fk')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->string('category', 30)->default('academic');
            $table->text('statement')->nullable();
            $table->text('basis')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'id', 'pts_std_approved_fk')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code'], 'pts_std_pt_code_unique');
            $table->index(['perguruan_tinggi_id', 'category', 'status'], 'pts_std_pt_status_idx');
        });

        Schema::table('spmi_standards', function (Blueprint $table): void {
            $table->foreignId('perguruan_tinggi_standard_id')
                ->nullable()
                ->after('spmi_framework_id')
                ->constrained('perguruan_tinggi_standards', 'id', 'spmi_std_pt_fk')
                ->nullOnDelete();
            $table->index('perguruan_tinggi_standard_id', 'spmi_std_pt_idx');
        });
    }

    public function down(): void
    {
        Schema::table('spmi_standards', function (Blueprint $table): void {
            $table->dropIndex('spmi_std_pt_idx');
            $table->dropForeign('spmi_std_pt_fk');
            $table->dropColumn('perguruan_tinggi_standard_id');
        });

        Schema::dropIfExists('perguruan_tinggi_standards');
    }
};
