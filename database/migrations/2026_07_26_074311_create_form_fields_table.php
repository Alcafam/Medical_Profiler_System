<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('type');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
