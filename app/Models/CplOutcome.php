<?php

namespace App\Models;

use App\Enums\CplCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CplOutcome extends Model
{
    /** @use HasFactory<\Database\Factories\CplOutcomeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program',
        'category',
        'code',
        'official_code',
        'description',
        'order_index',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => CplCategory::class,
            'order_index' => 'integer',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_cpl_outcome')
            ->withTimestamps();
    }
}
