<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    private GrpcClientManager $grpc;

    public function __construct(GrpcClientManager $grpc)
    {
        $this->grpc = $grpc;
    }

    public function upload(Tenant $tenant, DocumentType $documentType, \Illuminate\Http\UploadedFile $file): Document
    {
        // Store file
        $path = $file->store('documents/' . $tenant->id, 's3');
        
        // Create document record locally
        $document = Document::create([
            'tenant_id' => $tenant->id,
            'document_type_id' => $documentType->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        // Also create via gRPC for core processing
        $this->grpc->createDocument([
            'tenant_id' => (string) $tenant->id,
            'document_type_id' => (string) $documentType->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return $document;
    }

    public function get(Tenant $tenant, string $id): ?Document
    {
        return Document::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();
    }

    public function list(Tenant $tenant, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Document::where('tenant_id', $tenant->id)
            ->with(['documentType', 'pages', 'extractionResults'])
            ->latest();

        if (!empty($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('original_filename', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function updateStatus(Document $document, string $status): Document
    {
        $document->update([
            'status' => $status,
            'processed_at' => in_array($status, ['approved', 'rejected']) ? now() : null,
        ]);

        $this->grpc->updateDocument(
            (string) $document->id,
            (string) $document->tenant_id,
            ['status' => $this->mapStatusToGrpc($status)]
        );

        return $document;
    }

    public function delete(Document $document): void
    {
        // Delete from storage
        if (Storage::disk('s3')->exists($document->storage_path)) {
            Storage::disk('s3')->delete($document->storage_path);
        }

        // Delete pages
        foreach ($document->pages as $page) {
            if (Storage::disk('s3')->exists($page->storage_path)) {
                Storage::disk('s3')->delete($page->storage_path);
            }
        }

        $document->delete();
    }

    public function startProcessing(Document $document): \App\Models\ProcessingJob
    {
        $job = $this->grpc->createJob([
            'tenant_id' => (string) $document->tenant_id,
            'document_id' => (string) $document->id,
            'steps' => [
                ['name' => 'classify', 'config' => []],
                ['name' => 'ocr', 'config' => []],
                ['name' => 'extract', 'config' => []],
                ['name' => 'validate', 'config' => []],
                ['name' => 'export', 'config' => []],
            ],
        ]);

        // Create local job record
        $localJob = \App\Models\ProcessingJob::create([
            'tenant_id' => $document->tenant_id,
            'document_id' => $document->id,
            'status' => 'running',
            'current_step' => 0,
        ]);

        // Create job steps
        $stepNames = ['classify', 'ocr', 'extract', 'validate', 'export'];
        foreach ($stepNames as $index => $name) {
            \App\Models\JobStep::create([
                'job_id' => $localJob->id,
                'step_order' => $index,
                'name' => $name,
                'status' => $index === 0 ? 'running' : 'pending',
            ]);
        }

        // Update document status
        $this->updateStatus($document, 'queued');

        return $localJob;
    }

    private function mapStatusToGrpc(string $status): int
    {
        $map = [
            'uploaded' => 1,
            'queued' => 2,
            'processing' => 3,
            'review_required' => 4,
            'approved' => 5,
            'rejected' => 6,
            'failed' => 7,
            'archived' => 8,
        ];
        return $map[$status] ?? 1;
    }
}