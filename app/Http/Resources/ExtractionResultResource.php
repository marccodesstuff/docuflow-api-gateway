<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExtractionResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_id,
            'document_id' => $this->document_id,
            'status' => $this->status,
            'fields' => $this->fields,
            'tables' => $this->tables,
            'overall_confidence' => $this->overall_confidence,
            'model_version' => $this->model_version,
            'issues' => $this->issues,
            'extracted_at' => $this->created_at->toISOString(),
            'exports' => $this->whenLoaded('exports', function () {
                return ExportResource::collection($this->exports);
            }),
        ];
    }
}