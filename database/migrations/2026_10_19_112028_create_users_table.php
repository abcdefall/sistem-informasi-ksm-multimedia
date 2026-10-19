<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('role_id'); // FK ke roles
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->json('profile_data')->nullable();
            $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict');
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};