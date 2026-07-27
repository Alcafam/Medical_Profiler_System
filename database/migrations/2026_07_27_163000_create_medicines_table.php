<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('generic_name');
            $table->string('brand_name');
            $table->string('dosage_strength')->nullable();
            $table->date('expiration_date')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('quantity_dispensed')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
