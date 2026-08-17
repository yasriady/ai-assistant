<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalAssessment extends Model
{
    /** @use HasFactory<\Database\Factories\FinalAssessmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'submission_id',
        'answer_id',
        'ai_assessment_id',
        'score',
        'max_score',
        'feedback',
        'lecturer_notes',
        'finalized_by',
        'finalized_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function aiAssessment(): BelongsTo
    {
        return $this->belongsTo(AiAssessment::class);
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
