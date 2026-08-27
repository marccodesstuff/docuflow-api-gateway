<?php

namespace App\Filament\Resources\ProcessingJobResource\Pages;

use App\Filament\Resources\ProcessingJobResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProcessingJob extends CreateRecord
{
    protected static string $resource = ProcessingJobResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}