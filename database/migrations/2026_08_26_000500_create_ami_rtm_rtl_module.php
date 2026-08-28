<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ami_cycles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('instrument_version_id')->nullable()->constrained('instrument_versions')->nullOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->unsignedSmallInteger('period_year');
            $table->string('scope_type', 30)->default('institution');
            $table->string('status', 30)->default('draft');
            $table->date('planned_start')->nullable();
            $table->date('planned_end')->nullable();
            $table->date('actual_start')->nullable();
            $table->date('actual_end')->nullable();
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code'], 'ami_cycle_pt_code_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'period_year', 'status'], 'ami_cycle_scope_period_idx');
        });

        Schema::create('ami_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ami_cycle_id')->constrained('ami_cycles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('assignment_role', 30)->default('auditor');
            $table->string('status', 20)->default('invited');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['ami_cycle_id', 'user_id', 'assignment_role'], 'ami_assignment_unique');
        });

        Schema::create('ami_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ami_cycle_id')->constrained('ami_cycles')->cascadeOnDelete();
            $table->foreignId('instrument_node_id')->nullable()->constrained('instrument_nodes')->nullOnDelete();
            $table->foreignId('spmi_standard_id')->nullable()->constrained('spmi_standards')->nullOnDelete();
            $table->foreignId('spmi_indicator_id')->nullable()->constrained('spmi_indicators')->nullOnDelete();
            $table->string('code', 100);
            $table->text('question');
            $table->string('response_type', 30)->default('text');
            $table->string('response_status', 30)->default('not_started');
            $table->decimal('score', 10, 4)->nullable();
            $table->text('response')->nullable();
            $table->text('auditor_notes')->nullable();
            $table->boolean('evidence_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['ami_cycle_id', 'code'], 'ami_checklist_cycle_code_unique');
        });

        Schema::create('ami_findings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ami_cycle_id')->constrained('ami_cycles')->cascadeOnDelete();
            $table->foreignId('ami_checklist_item_id')->nullable()->constrained('ami_checklist_items')->nullOnDelete();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->string('code', 80);
            $table->string('classification', 30)->default('observation');
            $table->string('severity', 20)->default('medium');
            $table->text('requirement')->nullable();
            $table->text('condition');
            $table->text('criteria')->nullable();
            $table->text('cause')->nullable();
            $table->text('impact')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('status', 30)->default('open');
            $table->timestamps();
            $table->unique(['ami_cycle_id', 'code'], 'ami_finding_cycle_code_unique');
            $table->index(['ami_cycle_id', 'status', 'severity'], 'ami_finding_status_idx');
        });

        Schema::create('rtm_meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('ami_cycle_id')->nullable()->constrained('ami_cycles')->nullOnDelete();
            $table->string('code', 80);
            $table->string('title');
            $table->dateTime('held_at')->nullable();
            $table->string('status', 20)->default('planned');
            $table->text('minutes')->nullable();
            $table->foreignId('chair_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['perguruan_tinggi_id', 'code'], 'rtm_meeting_pt_code_unique');
        });

        Schema::create('rtm_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rtm_meeting_id')->constrained('rtm_meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role', 50)->nullable();
            $table->boolean('attended')->default(false);
            $table->timestamps();
            $table->unique(['rtm_meeting_id', 'user_id'], 'rtm_participant_unique');
        });

        Schema::create('rtm_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rtm_meeting_id')->constrained('rtm_meetings')->cascadeOnDelete();
            $table->foreignId('ami_finding_id')->nullable()->constrained('ami_findings')->nullOnDelete();
            $table->string('code', 80);
            $table->text('decision');
            $table->text('rationale')->nullable();
            $table->string('status', 20)->default('approved');
            $table->timestamps();
            $table->unique(['rtm_meeting_id', 'code'], 'rtm_decision_meeting_code_unique');
        });

        Schema::create('rtl_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('perguruan_tinggi_id')->constrained('perguruan_tinggi')->restrictOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->foreignId('ami_finding_id')->nullable()->constrained('ami_findings')->nullOnDelete();
            $table->foreignId('rtm_decision_id')->nullable()->constrained('rtm_decisions')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code', 80);
            $table->string('title');
            $table->text('action_plan');
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('status', 30)->default('open');
            $table->text('evidence_of_completion')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['perguruan_tinggi_id', 'code'], 'rtl_action_pt_code_unique');
            $table->index(['perguruan_tinggi_id', 'program_studi_id', 'status', 'due_date'], 'rtl_action_scope_status_due_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtl_actions');
        Schema::dropIfExists('rtm_decisions');
        Schema::dropIfExists('rtm_participants');
        Schema::dropIfExists('rtm_meetings');
        Schema::dropIfExists('ami_findings');
        Schema::dropIfExists('ami_checklist_items');
        Schema::dropIfExists('ami_assignments');
        Schema::dropIfExists('ami_cycles');
    }
};
