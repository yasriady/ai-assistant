<?php

namespace App\Models;

use App\Enums\AssessmentEngine;
use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'rubric_id',
        'assessment_template_id',
        'title',
        'description',
        'type',
        'engine',
        'instructions',
        'due_at',
        'max_score',
        'status',
        'settings',
        'rubric_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AssessmentType::class,
            'engine' => AssessmentEngine::class,
            'due_at' => 'datetime',
            'max_score' => 'decimal:2',
            'settings' => 'array',
            'rubric_version' => 'integer',
        ];
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

    public function assessmentTemplate(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class);
    }

    public function cpmks(): BelongsToMany
    {
        return $this->belongsToMany(Cpmk::class, 'assessment_cpmk')
            ->withTimestamps();
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order_index');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['id', 'order_index', 'max_score'])
            ->withTimestamps()
            ->orderByPivot('order_index');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function aiUsage(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }
}
