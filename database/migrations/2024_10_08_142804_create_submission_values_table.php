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
        Schema::create('submission_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id'); // Associated submission
            $table->unsignedBigInteger('form_field_id'); // Associated form field
            $table->text('value')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
            $table->foreign('form_field_id')->references('id')->on('form_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_values');
    }
};
