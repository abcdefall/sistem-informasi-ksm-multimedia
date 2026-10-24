<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sessions')->where('user_id', 0)->delete();

        Schema::table('sessions', function (Blueprint $table) {
            $table->string('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('sessions')->delete();

        Schema::table('sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
