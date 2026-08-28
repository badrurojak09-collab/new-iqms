<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->string('storage_provider', 50)->default('external_link')->after('uploaded_by');
            $table->text('external_url')->nullable()->after('storage_provider');
            $table->string('external_file_id', 255)->nullable()->after('external_url');
            $table->string('external_folder_url', 500)->nullable()->after('external_file_id');
            $table->string('link_access_mode', 30)->default('institution_managed')->after('external_folder_url');
            $table->timestamp('last_link_checked_at')->nullable()->after('status');
            $table->index(['storage_provider', 'status'], 'documents_provider_status_idx');
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->string('disk', 50)->nullable()->default(null)->change();
            $table->string('storage_path', 500)->nullable()->change();
            $table->string('original_name', 255)->nullable()->change();
            $table->string('mime_type', 150)->nullable()->change();
            $table->unsignedBigInteger('size_bytes')->nullable()->change();
            $table->char('sha256', 64)->nullable()->change();
            $table->string('visibility', 20)->nullable()->default('external')->change();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->dropIndex('documents_provider_status_idx');
            $table->dropColumn([
                'storage_provider',
                'external_url',
                'external_file_id',
                'external_folder_url',
                'link_access_mode',
                'last_link_checked_at',
            ]);
        });
    }
};
