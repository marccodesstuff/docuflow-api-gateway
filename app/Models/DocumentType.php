<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'fields',
        'validation_rules',
        'routing_rules',
    ];

    protected $casts = [
        'fields' => 'array',
        'validation_rules' => 'array',
        'routing_rules' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FieldDefinition::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}