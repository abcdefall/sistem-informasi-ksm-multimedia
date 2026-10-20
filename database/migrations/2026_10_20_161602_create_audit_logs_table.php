<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->string('log_id', 20)->primary();
            $table->string('user_id', 20)->nullable();
            $table->string('action', 100);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};