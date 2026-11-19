<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('archived_from_status', 50)->nullable()->after('status');
            $table->timestamp('archived_at')->nullable()->after('archived_from_status');
            $table->string('archived_by', 20)->nullable()->after('archived_at');

            $table->foreign('archived_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_from_status', 'archived_at', 'archived_by']);
        });
    }
};
