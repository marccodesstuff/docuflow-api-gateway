<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'document_type_id' => $this->document_type_id,
            'document_type' => $this->whenLoaded('documentType', function () {
                return [
                    'id' => $this->documentType->id,
                    'name' => $this->documentType->name,
                    'description' => $this->documentType->description,
                ];
            }),
            'original_filename' => $this->original_filename,
            'storage_path' => $this->storage_path,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'size_human' => $this->formatBytes($this->size_bytes),
            'status' => $this->status,
            'metadata' => $this->metadata,
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'pages' => $this->whenLoaded('pages', function () {
                return DocumentPageResource::collection($this->pages);
            }),
            'extraction_results' => $this->whenLoaded('extractionResults', function () {
                return ExtractionResultResource::collection($this->extractionResults);
            }),
            'processing_jobs' => $this->whenLoaded('processingJobs', function () {
                return ProcessingJobResource::collection($this->processingJobs);
            }),
            'urls' => [
                'download' => route('api.documents.download', $this->id),
                'process' => route('api.documents.process', $this->id),
            ],
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}