<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_files', function (Blueprint $table) {
            $table->string('file_id', 20)->primary();
            $table->string('project_id', 20);
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_files');
    }
};