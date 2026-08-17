<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Answer extends Model
{
    /** @use HasFactory<\Database\Factories\AnswerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'submission_id',
        'exam_question_id',
        'question_id',
        'answer_text',
        'selected_option_id',
        'answer_data',
        'ai_score',
        'final_score',
        'max_score',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answer_data' => 'array',
            'ai_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'max_score' => 'decimal:2',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function examQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function aiAssessments(): HasMany
    {
        return $this->hasMany(AiAssessment::class);
    }

    public function finalAssessment(): HasOne
    {
        return $this->hasOne(FinalAssessment::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
