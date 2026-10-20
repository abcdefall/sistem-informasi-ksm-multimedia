<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_tools', function (Blueprint $table) {
            // Primary key string untuk format 'TOOL-001'
            $table->string('tool_id', 20)->primary();
            
            // Foreign key ke tabel projects bertipe string(20)
            $table->string('project_id', 20);
            
            $table->string('tool_name', 255);
            $table->timestamps();

            // Definisi Foreign Key dengan aturan CASCADE
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_tools');
    }
};