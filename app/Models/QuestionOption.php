<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionOptionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'question_id',
        'label',
        'option_text',
        'is_correct',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'selected_option_id');
    }
}
