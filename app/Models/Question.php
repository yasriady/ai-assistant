<?php

namespace App\Models;

use App\Enums\CognitiveLevel;
use App\Enums\Difficulty;
use App\Enums\QuestionScopeType;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'question_bank_id',
        'course_id',
        'user_id',
        'topic',
        'scope_type',
        'question_type',
        'question_text',
        'expected_answer',
        'key_concepts',
        'difficulty',
        'cognitive_level',
        'max_score',
        'rubric_id',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'question_type' => QuestionType::class,
            'scope_type' => QuestionScopeType::class,
            'difficulty' => Difficulty::class,
            'cognitive_level' => CognitiveLevel::class,
            'key_concepts' => 'array',
            'metadata' => 'array',
            'max_score' => 'decimal:2',
        ];
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_index');
    }

    public function cpmks(): BelongsToMany
    {
        return $this->belongsToMany(Cpmk::class, 'question_cpmk')
            ->withTimestamps();
    }

    public function courseTopics(): BelongsToMany
    {
        return $this->belongsToMany(CourseTopic::class, 'question_course_topic')
            ->withTimestamps();
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'exam_questions')
            ->withPivot(['id', 'order_index', 'max_score'])
            ->withTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
