<?php

namespace App\Models;

use App\Enums\JobProcessStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFileFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'submission_id',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'extraction_status',
        'extracted_text',
        'ocr_confidence',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'extraction_status' => JobProcessStatus::class,
            'ocr_confidence' => 'decimal:2',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
