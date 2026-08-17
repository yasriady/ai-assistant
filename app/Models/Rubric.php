<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends Model
{
    /** @use HasFactory<\Database\Factories\RubricFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'name',
        'description',
        'version',
        'is_template',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_template' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class)->orderBy('order_index');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
