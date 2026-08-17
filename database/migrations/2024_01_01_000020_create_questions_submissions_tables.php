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
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'user_id']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic')->nullable();
            $table->string('question_type');
            $table->text('question_text');
            $table->longText('expected_answer')->nullable();
            $table->json('key_concepts')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('cognitive_level')->nullable();
            $table->decimal('max_score', 8, 2)->default(10);
            $table->foreignId('rubric_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'question_type']);
            $table->index(['question_bank_id', 'topic']);
            $table->index('difficulty');
            $table->index('cognitive_level');
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'order_index']);
        });

        Schema::create('question_cpmk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['question_id', 'cpmk_id']);
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('order_index')->default(0);
            $table->decimal('max_score', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'question_id']);
            $table->index(['assessment_id', 'order_index']);
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('uploaded');
            $table->longText('extracted_text')->nullable();
            $table->decimal('ai_score', 8, 2)->nullable();
            $table->decimal('final_score', 8, 2)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
            $table->index('status');
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('extraction_status')->default('pending');
            $table->longText('extracted_text')->nullable();
            $table->decimal('ocr_confidence', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'extraction_status']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_question_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('question_id')->nullable()->constrained()->nullOnDelete();
            $table->longText('answer_text')->nullable();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->json('answer_data')->nullable();
            $table->decimal('ai_score', 8, 2)->nullable();
            $table->decimal('final_score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'question_id']);
            $table->index('exam_question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('question_cpmk');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_banks');
    }
};
