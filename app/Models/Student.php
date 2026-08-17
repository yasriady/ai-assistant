<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nim',
        'name',
        'email',
        'program',
        'class_name',
    ];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
