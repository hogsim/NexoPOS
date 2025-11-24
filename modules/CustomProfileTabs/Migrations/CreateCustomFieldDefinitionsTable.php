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
        Schema::create('nexopos_custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Internal field name (e.g., 'company_name')
            $table->string('label'); // Display label (e.g., 'Company Name')
            $table->enum('type', ['text', 'textarea', 'number', 'email', 'select', 'datetime', 'switch', 'media'])->default('text');
            $table->enum('applies_to', ['user', 'customer']); // Which profile type
            $table->text('description')->nullable(); // Help text
            $table->json('options')->nullable(); // For select fields: {"option1": "Label 1", "option2": "Label 2"}
            $table->string('validation')->nullable(); // Validation rules (e.g., 'required|email')
            $table->integer('order')->default(0); // Display order
            $table->boolean('active')->default(true); // Enable/disable field
            $table->timestamps();

            // Unique constraint on name and applies_to
            $table->unique(['name', 'applies_to']);

            // Index for faster lookups
            $table->index(['applies_to', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_custom_field_definitions');
    }
};
