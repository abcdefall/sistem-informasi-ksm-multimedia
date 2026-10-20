<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_versions', function (Blueprint $table) {
            // Primary key string untuk format versi (misal: 'VER-001')
            $table->string('version_id', 20)->primary();
            
            // Foreign key ke tabel projects bertipe string(20)
            $table->string('project_id', 20);
            
            $table->string('version_number', 50); // Contoh: 'v1.0', 'v1.1'
            $table->text('changelog')->nullable(); // Catatan perubahan
            $table->string('file_path', 255)->nullable(); // Path file untuk versi ini
            $table->timestamps();

            // Definisi Foreign Key dengan aturan CASCADE
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_versions');
    }
};