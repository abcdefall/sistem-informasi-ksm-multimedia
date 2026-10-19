<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_members', function (Blueprint $table) {
            $table->id('project_member_id');
            $table->unsignedBigInteger('project_id'); // FK ke projects
            $table->unsignedBigInteger('user_id'); // FK ke users
            $table->string('member_role'); // Contoh: 'Project Manager', 'Lead Programmer'
            $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_members');
    }
};
