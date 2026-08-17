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
        Schema::create('cpl_outcomes', function (Blueprint $table) {
            $table->id();
            $table->string('program')->default('S1 Informatika');
            $table->string('category');
            $table->string('code', 10);
            $table->string('official_code', 20)->unique();
            $table->text('description');
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();

            $table->unique(['program', 'code']);
            $table->index(['program', 'category', 'order_index']);
        });

        Schema::create('course_cpl_outcome', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpl_outcome_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'cpl_outcome_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_cpl_outcome');
        Schema::dropIfExists('cpl_outcomes');
    }
};
