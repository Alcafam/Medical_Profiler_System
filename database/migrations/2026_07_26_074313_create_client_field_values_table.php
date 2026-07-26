<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'form_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_field_values');
    }
};
