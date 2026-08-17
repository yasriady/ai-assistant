<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseTopic extends Model
{
    /** @use HasFactory<\Database\Factories\CourseTopicFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'week_number',
        'title',
        'description',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_number' => 'integer',
            'order_index' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function cpmks(): BelongsToMany
    {
        return $this->belongsToMany(Cpmk::class, 'course_topic_cpmk')
            ->withTimestamps();
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_course_topic')
            ->withTimestamps();
    }
}
