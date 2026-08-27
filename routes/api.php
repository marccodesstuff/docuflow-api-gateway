<?php

use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentTypeController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\ProcessingJobController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    // Documents
    Route::apiResource('documents', DocumentController::class)
        ->only(['index', 'store', 'show', 'destroy']);
    Route::post('documents/{document}/process', [DocumentController::class, 'process'])
        ->name('documents.process');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
    Route::get('documents/{document}/pages/{page}/preview', [DocumentController::class, 'previewPage'])
        ->name('documents.pages.preview');

    // Document Types
    Route::apiResource('document-types', DocumentTypeController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    // Processing Jobs
    Route::apiResource('processing-jobs', ProcessingJobController::class)
        ->only(['index', 'show']);
    Route::post('processing-jobs/{job}/retry', [ProcessingJobController::class, 'retry'])
        ->name('processing-jobs.retry');
    Route::post('processing-jobs/{job}/cancel', [ProcessingJobController::class, 'cancel'])
        ->name('processing-jobs.cancel');

    // Webhooks
    Route::apiResource('webhooks', WebhookController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])
        ->name('webhooks.test');

    // Tenants
    Route::get('tenant', [TenantController::class, 'show'])->name('tenant.show');
    Route::put('tenant', [TenantController::class, 'update'])->name('tenant.update');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
})->name('health');