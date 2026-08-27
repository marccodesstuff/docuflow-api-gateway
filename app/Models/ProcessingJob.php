<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessingJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'document_id',
        'status',
        'current_step',
        'context',
        'started_at',
        'completed_at',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'context' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(JobStep::class)->orderBy('step_order');
    }
}