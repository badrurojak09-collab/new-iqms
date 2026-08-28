<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yayasan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('kode', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('kode');
        });

        Schema::create('perguruan_tinggi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('yayasan_id')->constrained('yayasan')->restrictOnDelete();
            $table->string('nama_pt');
            $table->string('kode_pt', 50);
            $table->string('jenis', 50)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['yayasan_id', 'kode_pt']);
            $table->index(['yayasan_id', 'status']);
        });

        Schema::create('program_studi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->string('nama_prodi');
            $table->string('kode_prodi', 50);
            $table->string('jenjang', 30)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'kode_prodi']);
            $table->index(['perguruan_tinggi_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('yayasan_id')->nullable()->after('id')->constrained('yayasan')->nullOnDelete();
            $table->foreignId('perguruan_tinggi_id')->nullable()->after('yayasan_id')->constrained('perguruan_tinggi')->nullOnDelete();
            $table->string('default_scope_type', 30)->default('institution')->after('perguruan_tinggi_id');
            $table->unsignedBigInteger('default_scope_id')->nullable()->after('default_scope_type');
            $table->index(['yayasan_id', 'perguruan_tinggi_id']);
            $table->index(['default_scope_type', 'default_scope_id']);
        });

        Schema::create('user_program_studi', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained('program_studi')->cascadeOnDelete();
            $table->string('peran', 50)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'program_studi_id']);
            $table->index(['program_studi_id', 'peran']);
        });

        Schema::create('user_tenant_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('scope_type', 30);
            $table->unsignedBigInteger('scope_id');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'scope_type', 'scope_id', 'role_id'], 'user_scope_unique');
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tenant_scopes');
        Schema::dropIfExists('user_program_studi');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['yayasan_id']);
            $table->dropForeign(['perguruan_tinggi_id']);
            $table->dropColumn([
                'yayasan_id',
                'perguruan_tinggi_id',
                'default_scope_type',
                'default_scope_id',
            ]);
        });
        Schema::dropIfExists('program_studi');
        Schema::dropIfExists('perguruan_tinggi');
        Schema::dropIfExists('yayasan');
    }
};
