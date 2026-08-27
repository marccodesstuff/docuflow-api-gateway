<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('process')
                ->label('Process Document')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn ($record) => in_array($record->status, ['uploaded', 'queued', 'failed']))
                ->action(fn ($record) => $record->update(['status' => 'queued'])),
            Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn ($record) => route('api.documents.download', $record->id), true),
        ];
    }
}