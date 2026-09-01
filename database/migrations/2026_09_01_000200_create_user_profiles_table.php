<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->unique();
            $table->string('nidn', 50)->nullable()->index();
            $table->string('nip', 50)->nullable()->index();
            $table->string('nik', 50)->nullable();
            $table->string('title_prefix', 50)->nullable();
            $table->string('title_suffix', 50)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('functional_position', 100)->nullable();
            $table->string('structural_position', 100)->nullable();
            $table->string('expertise', 255)->nullable();
            $table->text('address')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
