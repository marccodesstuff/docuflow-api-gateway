<?php

namespace App\Filament\Resources\ProcessingJobResource\Pages;

use App\Filament\Resources\ProcessingJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcessingJobs extends ListRecords
{
    protected static string $resource = ProcessingJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}