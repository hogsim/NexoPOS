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
        Schema::create('nexopos_custom_profile_tabs_customer_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('preferred_contact')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('referral_source')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('customer_id')
                ->references('id')
                ->on('nexopos_users')
                ->onDelete('cascade');

            // Index for faster lookups
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexopos_custom_profile_tabs_customer_data');
    }
};
