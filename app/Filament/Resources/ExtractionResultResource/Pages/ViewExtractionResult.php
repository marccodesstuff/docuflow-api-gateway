<?php

namespace App\Filament\Resources\ExtractionResultResource\Pages;

use App\Filament\Resources\ExtractionResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExtractionResult extends ViewRecord
{
    protected static string $resource = ExtractionResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}