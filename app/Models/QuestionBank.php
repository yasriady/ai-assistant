<?php

namespace App\Models;

use App\Enums\QuestionBankPurpose;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionBankFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'name',
        'description',
        'purpose',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => QuestionBankPurpose::class,
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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
