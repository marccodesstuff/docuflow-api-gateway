<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\DocumentTypeResource;
use App\Filament\Resources\ProcessingJobResource;
use App\Filament\Resources\ExtractionResultResource;
use App\Filament\Resources\WebhookResource;
use App\Filament\Resources\TenantResource;
use App\Filament\Resources\UserResource;

Route::middleware(['web', 'auth', 'verified'])->group(function () {
    // Filament admin panel routes are auto-registered by Filament
    // This file can be used for custom web routes
});

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});