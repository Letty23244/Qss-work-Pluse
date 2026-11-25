<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('workers', function (Blueprint $t) {
            $t->id();
            $t->string('employee_number')->nullable()->unique();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
        Schema::dropIfExists('teams');
    }
};
