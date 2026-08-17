<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricLevel extends Model
{
    /** @use HasFactory<\Database\Factories\RubricLevelFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rubric_criterion_id',
        'name',
        'description',
        'min_score',
        'max_score',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'order_index' => 'integer',
        ];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriterion::class, 'rubric_criterion_id');
    }
}
