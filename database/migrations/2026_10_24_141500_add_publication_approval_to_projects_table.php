<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->string('publication_approved_by', 20)->nullable()->after('published_at');
            $table->timestamp('publication_approved_at')->nullable()->after('publication_approved_by');
            $table->boolean('is_featured')->default(true)->after('publication_approved_at');

            $table->foreign('publication_approved_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['publication_approved_by']);
            $table->dropColumn(['publication_approved_by', 'publication_approved_at', 'is_featured']);
        });
    }
};
