<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->timestamp('queued_for_consultation_at')->nullable()->after('notes');
            $table->string('disposition')->nullable()->after('queued_for_consultation_at');
            $table->timestamp('disposition_at')->nullable()->after('disposition');

            $table->index(['disposition', 'queued_for_consultation_at']);
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex(['disposition', 'queued_for_consultation_at']);
            $table->dropColumn([
                'queued_for_consultation_at',
                'disposition',
                'disposition_at',
            ]);
        });
    }
};
