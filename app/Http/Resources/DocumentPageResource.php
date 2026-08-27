<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'page_number' => $this->page_number,
            'storage_path' => $this->storage_path,
            'width_px' => $this->width_px,
            'height_px' => $this->height_px,
            'elements' => $this->elements,
            'preview_url' => route('api.documents.pages.preview', ['document' => $this->document_id, 'page' => $this->page_number]),
        ];
    }
}