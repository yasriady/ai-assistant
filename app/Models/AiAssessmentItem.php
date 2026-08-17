<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAssessmentItem extends Model
{
    /** @use HasFactory<\Database\Factories\AiAssessmentItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ai_assessment_id',
        'criterion_name',
        'score',
        'max_score',
        'evidence',
        'reasoning',
        'feedback',
        'insufficient_evidence',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'insufficient_evidence' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    public function aiAssessment(): BelongsTo
    {
        return $this->belongsTo(AiAssessment::class);
    }
}
