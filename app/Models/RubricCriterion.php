<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricCriterion extends Model
{
    /** @use HasFactory<\Database\Factories\RubricCriterionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rubric_id',
        'name',
        'description',
        'weight',
        'max_score',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'max_score' => 'decimal:2',
            'order_index' => 'integer',
        ];
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(RubricLevel::class)->orderBy('order_index');
    }
}
