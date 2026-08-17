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
        Schema::create('ai_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->string('model_version')->nullable();
            $table->string('prompt_version')->nullable();
            $table->string('rubric_version')->nullable();
            $table->string('assessment_version')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->text('overall_feedback')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('structured_result')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempt')->default(1);
            $table->timestamps();

            $table->index(['submission_id', 'status']);
            $table->index(['provider', 'model']);
        });

        Schema::create('ai_assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_assessment_id')->constrained()->cascadeOnDelete();
            $table->string('criterion_name');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->text('evidence')->nullable();
            $table->text('reasoning')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('insufficient_evidence')->default(false);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['ai_assessment_id', 'order_index']);
        });

        Schema::create('final_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('score', 8, 2);
            $table->decimal('max_score', 8, 2);
            $table->text('feedback')->nullable();
            $table->text('lecturer_notes')->nullable();
            $table->foreignId('finalized_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('finalized_at');
            $table->timestamps();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_ai')->default(false);
            $table->timestamps();

            $table->index(['submission_id', 'is_ai']);
            $table->index('answer_id');
        });

        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->unsignedInteger('requests')->default(1);
            $table->decimal('estimated_cost', 12, 6)->default(0);
            $table->foreignId('assessment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'provider']);
            $table->index(['assessment_id', 'submission_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['user_id', 'action']);
            $table->index('created_at');
        });

        Schema::create('prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('system_prompt');
            $table->text('assessment_prompt')->nullable();
            $table->text('feedback_prompt')->nullable();
            $table->boolean('is_system')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('model');
            $table->decimal('temperature', 3, 2)->default(0.2);
            $table->unsignedInteger('max_tokens')->default(4000);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['provider', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
        Schema::dropIfExists('prompt_templates');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_usage');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('final_assessments');
        Schema::dropIfExists('ai_assessment_items');
        Schema::dropIfExists('ai_assessments');
    }
};
