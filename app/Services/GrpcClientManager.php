<?php

namespace App\Services;

use Grpc\ChannelCredentials;
use App\Grpc\Generated\Docuflow\V1\DocumentServiceClient;
use App\Grpc\Generated\Docuflow\V1\ProcessingJobServiceClient;
use App\Grpc\Generated\Docuflow\V1\ExtractionServiceClient;
use App\Grpc\Generated\Docuflow\V1\WebhookServiceClient;
use App\Grpc\Generated\Docuflow\V1\MLInferenceServiceClient;
use App\Grpc\Generated\Docuflow\V1\CreateDocumentRequest;
use App\Grpc\Generated\Docuflow\V1\GetDocumentRequest;
use App\Grpc\Generated\Docuflow\V1\UpdateDocumentRequest;
use App\Grpc\Generated\Docuflow\V1\ListDocumentsRequest;
use App\Grpc\Generated\Docuflow\V1\CreateJobRequest;
use App\Grpc\Generated\Docuflow\V1\GetJobRequest;
use App\Grpc\Generated\Docuflow\V1\ExtractDocumentRequest;
use App\Grpc\Generated\Docuflow\V1\RegisterWebhookRequest;
use App\Grpc\Generated\Docuflow\V1\ClassifyDocumentRequest;
use App\Grpc\Generated\Docuflow\V1\ExtractFieldsRequest;
use App\Grpc\Generated\Docuflow\V1\DetectTablesRequest;
use App\Grpc\Generated\Docuflow\V1\HealthCheckRequest;
use App\Grpc\Generated\Docuflow\V1\FieldDefinition;
use App\Grpc\Generated\Docuflow\V1\JobStepDefinition;
use Google\Protobuf\Internal\MapField;

class GrpcClientManager
{
    private DocumentServiceClient $documentClient;
    private ProcessingJobServiceClient $jobClient;
    private ExtractionServiceClient $extractionClient;
    private WebhookServiceClient $webhookClient;
    private MLInferenceServiceClient $mlClient;

    public function __construct()
    {
        $coreHost = config('grpc.core_host', 'localhost');
        $corePort = config('grpc.core_port', 9090);
        $mlHost = config('grpc.ml_host', 'localhost');
        $mlPort = config('grpc.ml_port', 50051);

        $coreChannel = new \Grpc\Channel(
            "{$coreHost}:{$corePort}",
            ChannelCredentials::createInsecure()
        );

        $mlChannel = new \Grpc\Channel(
            "{$mlHost}:{$mlPort}",
            ChannelCredentials::createInsecure()
        );

        $this->documentClient = new DocumentServiceClient($coreChannel);
        $this->jobClient = new ProcessingJobServiceClient($coreChannel);
        $this->extractionClient = new ExtractionServiceClient($coreChannel);
        $this->webhookClient = new WebhookServiceClient($coreChannel);
        $this->mlClient = new MLInferenceServiceClient($mlChannel);
    }

    // Document Service
    public function createDocument(array $data): \App\Grpc\Generated\Docuflow\V1\Document
    {
        $request = new CreateDocumentRequest();
        $request->setTenantId($data['tenant_id']);
        $request->setDocumentTypeId($data['document_type_id']);
        $request->setOriginalFilename($data['original_filename']);
        $request->setStoragePath($data['storage_path']);
        $request->setMimeType($data['mime_type']);
        $request->setSizeBytes($data['size_bytes']);
        if (isset($data['metadata'])) {
            foreach ($data['metadata'] as $k => $v) {
                $request->getMetadata()->set($k, $v);
            }
        }

        list($response, $status) = $this->documentClient->CreateDocument($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function getDocument(string $id, string $tenantId): \App\Grpc\Generated\Docuflow\V1\Document
    {
        $request = new GetDocumentRequest();
        $request->setId($id);
        $request->setTenantId($tenantId);

        list($response, $status) = $this->documentClient->GetDocument($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function updateDocument(string $id, string $tenantId, array $data): \App\Grpc\Generated\Docuflow\V1\Document
    {
        $request = new UpdateDocumentRequest();
        $request->setId($id);
        $request->setTenantId($tenantId);
        if (isset($data['status'])) {
            $request->setStatus($data['status']);
        }
        if (isset($data['metadata'])) {
            foreach ($data['metadata'] as $k => $v) {
                $request->getMetadata()->set($k, $v);
            }
        }

        list($response, $status) = $this->documentClient->UpdateDocument($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function listDocuments(string $tenantId, array $filters = []): array
    {
        $request = new ListDocumentsRequest();
        $request->setTenantId($tenantId);
        $request->setPage($filters['page'] ?? 1);
        $request->setPageSize($filters['page_size'] ?? 20);
        if (!empty($filters['document_type_id'])) {
            $request->setDocumentTypeId($filters['document_type_id']);
        }
        if (!empty($filters['statuses'])) {
            foreach ($filters['statuses'] as $s) {
                $request->getStatuses()->append($s);
            }
        }

        list($response, $status) = $this->documentClient->ListDocuments($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return [
            'documents' => iterator_to_array($response->getDocuments()),
            'total' => $response->getTotal(),
            'page' => $response->getPage(),
            'page_size' => $response->getPageSize(),
        ];
    }

    // Processing Job Service
    public function createJob(array $data): \App\Grpc\Generated\Docuflow\V1\ProcessingJob
    {
        $request = new CreateJobRequest();
        $request->setTenantId($data['tenant_id']);
        $request->setDocumentId($data['document_id']);
        if (isset($data['steps'])) {
            foreach ($data['steps'] as $step) {
                $stepDef = new JobStepDefinition();
                $stepDef->setName($step['name']);
                if (isset($step['config'])) {
                    foreach ($step['config'] as $k => $v) {
                        $stepDef->getConfig()->set($k, $v);
                    }
                }
                $request->getSteps()->append($stepDef);
            }
        }
        if (isset($data['context'])) {
            foreach ($data['context'] as $k => $v) {
                $request->getContext()->set($k, $v);
            }
        }

        list($response, $status) = $this->jobClient->CreateJob($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function getJob(string $id, string $tenantId): \App\Grpc\Generated\Docuflow\V1\ProcessingJob
    {
        $request = new GetJobRequest();
        $request->setId($id);
        $request->setTenantId($tenantId);

        list($response, $status) = $this->jobClient->GetJob($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    // Extraction Service
    public function extractDocument(array $data): \App\Grpc\Generated\Docuflow\V1\ExtractionResult
    {
        $request = new ExtractDocumentRequest();
        $request->setJobId($data['job_id']);
        $request->setDocumentId($data['document_id']);
        $request->setDocumentTypeId($data['document_type_id']);
        $request->setStoragePath($data['storage_path']);
        $request->setMimeType($data['mime_type']);
        if (isset($data['model_config'])) {
            foreach ($data['model_config'] as $k => $v) {
                $request->getModelConfig()->set($k, $v);
            }
        }

        list($response, $status) = $this->extractionClient->ExtractDocument($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    // Webhook Service
    public function registerWebhook(array $data): \App\Grpc\Generated\Docuflow\V1\WebhookRegistration
    {
        $request = new RegisterWebhookRequest();
        $request->setTenantId($data['tenant_id']);
        $request->setUrl($data['url']);
        if (isset($data['events'])) {
            foreach ($data['events'] as $e) {
                $request->getEvents()->append($e);
            }
        }
        if (isset($data['secret'])) {
            $request->setSecret($data['secret']);
        }

        list($response, $status) = $this->webhookClient->RegisterWebhook($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    // ML Inference Service
    public function classifyDocument(array $data): \App\Grpc\Generated\Docuflow\V1\ClassifyDocumentResponse
    {
        $request = new ClassifyDocumentRequest();
        $request->setDocumentId($data['document_id']);
        $request->setStoragePath($data['storage_path']);
        $request->setMimeType($data['mime_type']);
        if (isset($data['candidate_types'])) {
            foreach ($data['candidate_types'] as $t) {
                $request->getCandidateTypes()->append($t);
            }
        }

        list($response, $status) = $this->mlClient->ClassifyDocument($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function extractFields(array $data): \App\Grpc\Generated\Docuflow\V1\ExtractFieldsResponse
    {
        $request = new ExtractFieldsRequest();
        $request->setDocumentId($data['document_id']);
        $request->setStoragePath($data['storage_path']);
        $request->setMimeType($data['mime_type']);
        $request->setDocumentTypeId($data['document_type_id']);
        if (isset($data['field_schema'])) {
            foreach ($data['field_schema'] as $key => $fd) {
                $fieldDef = new FieldDefinition();
                $fieldDef->setKey($fd['key'] ?? '');
                $fieldDef->setLabel($fd['label'] ?? '');
                $fieldDef->setType($fd['type'] ?? \App\Grpc\Generated\Docuflow\V1\FieldDefinition\FieldType::FIELD_TYPE_STRING);
                $fieldDef->setRequired($fd['required'] ?? false);
                $fieldDef->setDescription($fd['description'] ?? '');
                $request->getFieldSchema()->set($key, $fieldDef);
            }
        }

        list($response, $status) = $this->mlClient->ExtractFields($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function detectTables(array $data): \App\Grpc\Generated\Docuflow\V1\DetectTablesResponse
    {
        $request = new DetectTablesRequest();
        $request->setDocumentId($data['document_id']);
        $request->setStoragePath($data['storage_path']);
        $request->setMimeType($data['mime_type']);
        $request->setMaxTables($data['max_tables'] ?? 10);

        list($response, $status) = $this->mlClient->DetectTables($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }

    public function healthCheck(): \App\Grpc\Generated\Docuflow\V1\HealthCheckResponse
    {
        $request = new HealthCheckRequest();
        list($response, $status) = $this->mlClient->HealthCheck($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \RuntimeException("gRPC error: {$status->details}");
        }
        return $response;
    }
}