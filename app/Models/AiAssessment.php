<?php

namespace App\Models;

use App\Enums\JobProcessStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiAssessment extends Model
{
    /** @use HasFactory<\Database\Factories\AiAssessmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'submission_id',
        'answer_id',
        'provider',
        'model',
        'model_version',
        'prompt_version',
        'rubric_version',
        'assessment_version',
        'status',
        'score',
        'max_score',
        'confidence',
        'overall_feedback',
        'raw_response',
        'structured_result',
        'error_message',
        'attempt',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => JobProcessStatus::class,
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'confidence' => 'decimal:4',
            'raw_response' => 'array',
            'structured_result' => 'array',
            'attempt' => 'integer',
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

    public function items(): HasMany
    {
        return $this->hasMany(AiAssessmentItem::class)->orderBy('order_index');
    }

    public function finalAssessment(): HasOne
    {
        return $this->hasOne(FinalAssessment::class);
    }
}
