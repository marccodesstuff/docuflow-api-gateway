<?php

namespace App\Filament\Resources\ExtractionResultResource\Pages;

use App\Filament\Resources\ExtractionResultResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExtractionResult extends CreateRecord
{
    protected static string $resource = ExtractionResultResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}