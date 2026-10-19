<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->unsignedBigInteger('field_id'); // FK ke mentoring_fields
            $table->unsignedBigInteger('period_id'); // FK ke mentoring_periods
            $table->unsignedBigInteger('created_by'); // FK ke users
            
            $table->string('title');
            $table->text('description');
            $table->string('thumbnail_url')->nullable();
            $table->string('project_link')->nullable();
            
            $table->enum('status', [
                'Draft', 'Submitted', 'In Review', 'Revision Required', 
                'Resubmitted', 'Approved', 'Published', 'Archived'
            ])->default('Draft');
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('field_id')->references('field_id')->on('mentoring_fields')->onDelete('restrict');
            $table->foreign('period_id')->references('period_id')->on('mentoring_periods')->onDelete('restrict');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('projects');
    }
};
