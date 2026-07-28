<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('consultation_locked_by')
                ->nullable()
                ->after('disposition_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('consultation_locked_at')->nullable()->after('consultation_locked_by');

            $table->index(['consultation_locked_by', 'consultation_locked_at']);
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consultation_locked_by');
            $table->dropColumn('consultation_locked_at');
        });
    }
};
