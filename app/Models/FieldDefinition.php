<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'parent_id',
        'sort_order',
        'key',
        'label',
        'type',
        'required',
        'description',
        'regex_pattern',
        'enum_values',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'required' => 'boolean',
        'enum_values' => 'array',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}