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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('semester')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('class_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'code']);
            $table->index('is_active');
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('program')->nullable();
            $table->string('class_name')->nullable();
            $table->timestamps();
        });

        Schema::create('course_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });

        Schema::create('cpmks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->text('description');
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'order_index']);
            $table->unique(['course_id', 'code']);
        });

        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_template')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_template']);
        });

        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('max_score', 8, 2);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['rubric_id', 'order_index']);
        });

        Schema::create('rubric_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_criterion_id')->constrained('rubric_criteria')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('min_score', 8, 2);
            $table->decimal('max_score', 8, 2);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['rubric_criterion_id', 'order_index']);
        });

        Schema::create('assessment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('assessment_type');
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'assessment_type']);
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('engine');
            $table->text('instructions')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->decimal('max_score', 8, 2)->default(100);
            $table->string('status')->default('draft');
            $table->json('settings')->nullable();
            $table->unsignedInteger('rubric_version')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index('engine');
            $table->index('due_at');
        });

        Schema::create('assessment_cpmk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['assessment_id', 'cpmk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_cpmk');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_templates');
        Schema::dropIfExists('rubric_levels');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');
        Schema::dropIfExists('cpmks');
        Schema::dropIfExists('course_student');
        Schema::dropIfExists('students');
        Schema::dropIfExists('courses');
    }
};
