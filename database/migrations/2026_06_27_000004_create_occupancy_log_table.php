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
        Schema::create('occupancy_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('camera_zone_id')->constrained('camera_zones')->onDelete('cascade');
            $table->jsonb('detected_objects_coords');
            
            // Just created_at, it's a log so updated_at is unnecessary
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // Add GIN index for PostgreSQL
        DB::statement('CREATE INDEX occupancy_log_detected_objects_coords_gin ON occupancy_log USING GIN (detected_objects_coords)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupancy_log');
    }
};
