<?php

namespace App\Filament\Resources\ProcessingJobResource\Pages;

use App\Filament\Resources\ProcessingJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessingJob extends EditRecord
{
    protected static string $resource = ProcessingJobResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}