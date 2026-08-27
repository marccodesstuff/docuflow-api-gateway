<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentUploadRequest;
use App\Http\Requests\DocumentListRequest;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\DocumentCollection;
use App\Models\Document;
use App\Models\DocumentType;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    private DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(DocumentListRequest $request): DocumentCollection
    {
        $tenant = $request->user()->tenant;
        $documents = $this->documentService->list($tenant, $request->validated());
        return new DocumentCollection($documents);
    }

    public function store(DocumentUploadRequest $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $documentType = DocumentType::where('tenant_id', $tenant->id)
            ->where('id', $request->document_type_id)
            ->firstOrFail();

        $document = $this->documentService->upload($tenant, $documentType, $request->file('file'));

        return response()->json([
            'data' => new DocumentResource($document->load(['documentType', 'pages'])),
            'message' => 'Document uploaded successfully',
        ], 201);
    }

    public function show(string $id): DocumentResource
    {
        $tenant = request()->user()->tenant;
        $document = Document::where('tenant_id', $tenant->id)
            ->with(['documentType', 'pages', 'extractionResults', 'processingJobs.steps'])
            ->findOrFail($id);

        return new DocumentResource($document);
    }

    public function process(string $id): JsonResponse
    {
        $tenant = request()->user()->tenant;
        $document = Document::where('tenant_id', $tenant->id)
            ->findOrFail($id);

        if (!in_array($document->status, ['uploaded', 'queued', 'failed'])) {
            return response()->json([
                'message' => 'Document cannot be processed in current status',
            ], 422);
        }

        $job = $this->documentService->startProcessing($document);

        return response()->json([
            'data' => [
                'job_id' => $job->id,
                'status' => $job->status,
            ],
            'message' => 'Processing started',
        ]);
    }

    public function download(string $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $tenant = request()->user()->tenant;
        $document = Document::where('tenant_id', $tenant->id)
            ->findOrFail($id);

        $disk = \Illuminate\Support\Facades\Storage::disk('s3');
        $path = $document->storage_path;

        if (!$disk->exists($path)) {
            abort(404);
        }

        return response()->streamDownload(function () use ($disk, $path) {
            echo $disk->get($path);
        }, $document->original_filename, [
            'Content-Type' => $document->mime_type,
            'Content-Length' => $document->size_bytes,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $tenant = request()->user()->tenant;
        $document = Document::where('tenant_id', $tenant->id)
            ->findOrFail($id);

        $this->documentService->delete($document);

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }
}