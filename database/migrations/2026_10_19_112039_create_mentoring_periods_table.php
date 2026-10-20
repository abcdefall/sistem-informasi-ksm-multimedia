<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mentoring_periods', function (Blueprint $table) {
            $table->string('period_id', 20)->primary();
            $table->string('period_name', 255); // Contoh: "Semester Ganjil 2026/2027"
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('mentoring_periods');
    }
};
