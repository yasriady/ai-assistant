<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'assessment_id',
        'student_id',
        'status',
        'extracted_text',
        'ai_score',
        'final_score',
        'processed_at',
        'reviewed_at',
        'finalized_at',
        'failure_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubmissionStatus::class,
            'ai_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'processed_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
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

    public function aiUsage(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }
}
