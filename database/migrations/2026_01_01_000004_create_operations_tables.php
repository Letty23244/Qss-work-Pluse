<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->foreignId('team_id')->constrained()->cascadeOnDelete();
            $t->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $t->date('date');
            $t->string('shift_type');                       // morning | afternoon | night
            $t->time('start_time');
            $t->time('end_time');
            $t->string('status')->default('scheduled');    // scheduled | active | completed
            $t->unsignedInteger('workers_deployed')->default(0);
            $t->timestamps();
            $t->unique(['team_id', 'date', 'shift_type']);
        });

        Schema::create('work_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->foreignId('team_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('activity');
            $t->text('description')->nullable();
            $t->string('unit')->default('units');
            $t->decimal('expected_output', 14, 2)->default(0);
            $t->date('start_date');
            $t->date('due_date')->nullable();
            $t->string('status')->default('pending');      // pending | in_progress | completed
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('work_assignment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->string('shift_type')->nullable();          // null = whole-day target
            $t->string('activity')->nullable();
            $t->decimal('quantity', 14, 2);
            $t->string('unit')->nullable();
            $t->text('notes')->nullable();
            $t->text('last_adjustment_reason')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('target_adjustments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('target_id')->constrained()->cascadeOnDelete();
            $t->decimal('old_quantity', 14, 2);
            $t->decimal('new_quantity', 14, 2);
            $t->text('reason')->nullable();
            $t->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('production_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $t->foreignId('work_assignment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->foreignId('team_id')->constrained()->cascadeOnDelete();
            $t->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $t->date('date');
            $t->string('shift_type');
            $t->decimal('quantity_produced', 14, 2);
            $t->decimal('quantity_pending', 14, 2)->default(0);
            $t->text('remarks')->nullable();
            $t->string('status')->default('submitted');    // submitted | approved | rejected
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
            $t->unique(['shift_id', 'work_assignment_id']);
            $t->index(['client_id', 'date']);
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('event');                            // created | updated | deleted
            $t->string('auditable_type');
            $t->unsignedBigInteger('auditable_id');
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('ip_address')->nullable();
            $t->timestamps();
            $t->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'production_records', 'target_adjustments', 'targets', 'work_assignments', 'shifts'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
