<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAcademicTerm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use BelongsToAcademicTerm;
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'term_code',
        'semester',
        'academic_year',
        'class_name',
        'description',
        'midterm_week',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'midterm_week' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(Cpmk::class)->orderBy('order_index');
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(Rubric::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(CourseTopic::class)->orderBy('order_index')->orderBy('week_number');
    }

    public function cplOutcomes(): BelongsToMany
    {
        return $this->belongsToMany(CplOutcome::class, 'course_cpl_outcome')
            ->withTimestamps();
    }
}
