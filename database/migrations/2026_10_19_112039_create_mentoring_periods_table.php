<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mentoring_periods', function (Blueprint $table) {
            $table->id('period_id');
            $table->string('period_name'); // Contoh: "Semester Ganjil 2026/2027"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('mentoring_periods');
    }
};
