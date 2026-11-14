<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('people_counts', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->timestamp('fecha_hora')->useCurrent();
            $table->timestamps();
        });

        // View alias for compatibility
        DB::statement('CREATE OR REPLACE VIEW conteo_personas AS SELECT * FROM people_counts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS conteo_personas CASCADE');
        Schema::dropIfExists('people_counts');
    }
};
