<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('camera_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('camera_id')->constrained('cameras')->onDelete('cascade');
            $table->jsonb('geometry')->nullable();
            $table->decimal('total_length_meters', 5, 2);
            $table->jsonb('homography_matrix')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camera_zones');
    }
};
