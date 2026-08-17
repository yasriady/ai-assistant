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
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedTinyInteger('midterm_week')->default(8)->after('description');
        });

        Schema::create('course_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'week_number']);
            $table->index(['course_id', 'order_index']);
        });

        Schema::create('course_topic_cpmk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_topic_id', 'cpmk_id']);
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->string('purpose')->nullable()->after('description');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('scope_type')->default('specific')->after('topic');
        });

        Schema::create('question_course_topic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_topic_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['question_id', 'course_topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_course_topic');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('scope_type');
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });

        Schema::dropIfExists('course_topic_cpmk');
        Schema::dropIfExists('course_topics');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('midterm_week');
        });
    }
};
