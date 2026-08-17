<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\PromptTemplateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'name',
        'system_prompt',
        'assessment_prompt',
        'feedback_prompt',
        'is_system',
        'version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'version' => 'integer',
        ];
    }
}
