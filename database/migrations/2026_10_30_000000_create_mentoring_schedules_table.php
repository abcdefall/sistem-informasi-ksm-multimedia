<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mentoring_schedules', function (Blueprint $table): void {
            $table->string('schedule_id', 20)->primary();
            $table->string('project_id', 20);
            $table->string('mentor_id', 20);
            $table->dateTime('starts_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('location')->nullable();
            $table->string('meeting_url', 2048)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Scheduled', 'Canceled'])->default('Scheduled');
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('mentor_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->index(['mentor_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_schedules');
    }
};
