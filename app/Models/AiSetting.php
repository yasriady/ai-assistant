<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    /** @use HasFactory<\Database\Factories\AiSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'provider',
        'model',
        'temperature',
        'max_tokens',
        'is_active',
        'config',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:2',
            'max_tokens' => 'integer',
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }
}
