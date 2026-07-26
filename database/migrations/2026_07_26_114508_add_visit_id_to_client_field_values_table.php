<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('client_field_values', 'visit_id')) {
            Schema::table('client_field_values', function (Blueprint $table) {
                $table->foreignId('visit_id')->nullable()->after('client_id')->constrained()->cascadeOnDelete();
            });
        }

        $clients = DB::table('clients')->select('id', 'created_at', 'created_by')->get();

        foreach ($clients as $client) {
            $existingVisitId = DB::table('visits')->where('client_id', $client->id)->value('id');

            if (! $existingVisitId) {
                $existingVisitId = DB::table('visits')->insertGetId([
                    'client_id' => $client->id,
                    'visited_at' => $client->created_at ?? now(),
                    'created_by' => $client->created_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('client_field_values')
                ->where('client_id', $client->id)
                ->whereNull('visit_id')
                ->update(['visit_id' => $existingVisitId]);
        }

        DB::table('client_field_values')->whereNull('visit_id')->delete();

        // MySQL may use the composite unique index for the client_id FK — add a dedicated index first.
        Schema::table('client_field_values', function (Blueprint $table) {
            $sm = Schema::getConnection()->getSchemaBuilder();
            $indexes = collect(DB::select('show index from client_field_values'))->pluck('Key_name')->unique();

            if (! $indexes->contains('client_field_values_client_id_index')) {
                $table->index('client_id');
            }
        });

        Schema::table('client_field_values', function (Blueprint $table) {
            $indexes = collect(DB::select('show index from client_field_values'))->pluck('Key_name')->unique();

            if ($indexes->contains('client_field_values_client_id_form_field_id_unique')) {
                $table->dropUnique(['client_id', 'form_field_id']);
            }
        });

        Schema::table('client_field_values', function (Blueprint $table) {
            $indexes = collect(DB::select('show index from client_field_values'))->pluck('Key_name')->unique();

            if (! $indexes->contains('client_field_values_visit_id_form_field_id_unique')) {
                $table->unique(['visit_id', 'form_field_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_field_values', function (Blueprint $table) {
            $indexes = collect(DB::select('show index from client_field_values'))->pluck('Key_name')->unique();

            if ($indexes->contains('client_field_values_visit_id_form_field_id_unique')) {
                $table->dropUnique(['visit_id', 'form_field_id']);
            }
        });

        Schema::table('client_field_values', function (Blueprint $table) {
            if (Schema::hasColumn('client_field_values', 'visit_id')) {
                $table->dropConstrainedForeignId('visit_id');
            }
        });

        Schema::table('client_field_values', function (Blueprint $table) {
            $indexes = collect(DB::select('show index from client_field_values'))->pluck('Key_name')->unique();

            if (! $indexes->contains('client_field_values_client_id_form_field_id_unique')) {
                $table->unique(['client_id', 'form_field_id']);
            }
        });

        DB::table('visits')->delete();
    }
};
