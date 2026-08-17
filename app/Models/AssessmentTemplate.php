<?php

namespace App\Models;

use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\AssessmentTemplateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'assessment_type',
        'description',
        'config',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assessment_type' => AssessmentType::class,
            'config' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
