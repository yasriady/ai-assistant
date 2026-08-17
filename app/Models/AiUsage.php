<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    /** @use HasFactory<\Database\Factories\AiUsageFactory> */
    use HasFactory;

    protected $table = 'ai_usage';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'tokens_input',
        'tokens_output',
        'requests',
        'estimated_cost',
        'assessment_id',
        'submission_id',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tokens_input' => 'integer',
            'tokens_output' => 'integer',
            'requests' => 'integer',
            'estimated_cost' => 'decimal:6',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
