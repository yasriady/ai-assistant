<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /** @use HasFactory<\Database\Factories\FeedbackFactory> */
    use HasFactory;

    protected $table = 'feedback';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'submission_id',
        'answer_id',
        'user_id',
        'body',
        'is_ai',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ai' => 'boolean',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
