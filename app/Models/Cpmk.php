<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cpmk extends Model
{
    /** @use HasFactory<\Database\Factories\CpmkFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'code',
        'description',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_index' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_cpmk')
            ->withTimestamps();
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_cpmk')
            ->withTimestamps();
    }

    public function courseTopics(): BelongsToMany
    {
        return $this->belongsToMany(CourseTopic::class, 'course_topic_cpmk')
            ->withTimestamps();
    }
}
