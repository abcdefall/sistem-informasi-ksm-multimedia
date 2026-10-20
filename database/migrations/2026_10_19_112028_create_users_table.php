<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id', 20)->primary();
            
            $table->string('role_id', 20); // FK ke roles
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict')->onUpdate('cascade');
           
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);

            $table->json('profile_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};