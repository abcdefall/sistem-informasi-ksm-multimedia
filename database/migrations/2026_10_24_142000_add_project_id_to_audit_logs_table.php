<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->string('project_id', 20)->nullable()->after('user_id');
            $table->foreign('project_id')
                ->references('project_id')
                ->on('projects')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
