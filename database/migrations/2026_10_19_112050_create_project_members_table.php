<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_members', function (Blueprint $table) {
            $table->id('project_member_id', 20);
            $table->string('project_id', 20); // FK ke projects
            $table->string('user_id', 20); // FK ke users
            $table->string('member_role', 100); // Contoh: 'Project Manager', 'Lead Programmer'
            $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_members');
    }
};
