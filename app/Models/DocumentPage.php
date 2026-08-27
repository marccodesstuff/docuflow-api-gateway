<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'page_number',
        'storage_path',
        'width_px',
        'height_px',
        'elements',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'width_px' => 'integer',
        'height_px' => 'integer',
        'elements' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}