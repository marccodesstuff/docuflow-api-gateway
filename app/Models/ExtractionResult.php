<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtractionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'document_id',
        'status',
        'fields',
        'tables',
        'overall_confidence',
        'model_version',
        'issues',
    ];

    protected $casts = [
        'fields' => 'array',
        'tables' => 'array',
        'overall_confidence' => 'float',
        'issues' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProcessingJob::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}