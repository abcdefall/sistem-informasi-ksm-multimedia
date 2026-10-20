<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mentoring_fields', function (Blueprint $table) {
            $table->string('field_id', 20)->primary();
            $table->enum('field_name', ['Web Dev', 'Game Dev', 'UI/UX', 'Graphic Design']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('mentoring_fields');
    }
};
