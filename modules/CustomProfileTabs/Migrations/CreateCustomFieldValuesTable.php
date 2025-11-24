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
        Schema::create('nexopos_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id'); // Reference to custom_field_definitions
            $table->unsignedBigInteger('entity_id'); // User ID or Customer ID
            $table->enum('entity_type', ['user', 'customer']); // Type of entity
            $table->text('value')->nullable(); // The actual field value
            $table->timestamps();

            // Foreign key to field definitions
            $table->foreign('field_id')
                ->references('id')
                ->on('nexopos_custom_field_definitions')
                ->onDelete('cascade');

            // Indexes for fast lookups
            $table->index(['entity_id', 'entity_type']);
            $table->index('field_id');

            // Ensure one value per field per entity
            $table->unique(['field_id', 'entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_custom_field_values');
    }
};
