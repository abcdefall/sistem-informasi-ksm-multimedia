<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->string('review_id', 20)->primary();
            $table->string('project_id', 20);
            $table->string('reviewer_id', 20); // Relasi ke users (mentor/pengurus)
            $table->text('comment');
            $table->enum('rating', ['1', '2', '3', '4', '5'])->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('reviewer_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};