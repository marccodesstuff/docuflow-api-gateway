<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'step_order',
        'name',
        'status',
        'input',
        'output',
        'error_message',
        'retry_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'input' => 'array',
        'output' => 'array',
        'retry_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ProcessingJob::class);
    }
}