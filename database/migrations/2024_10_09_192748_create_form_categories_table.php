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
        Schema::create('form_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order');
            $table->decimal('percentage_start', 5, 2);
            $table->decimal('percentage_end', 5, 2);
            $table->timestamps();
        });

        Schema::table('form_fields', function (Blueprint $table) {
            $table->foreignId('form_category_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropForeign(['form_category_id']);
            $table->dropColumn('form_category_id');
        });

        Schema::dropIfExists('form_categories');
    }
};
