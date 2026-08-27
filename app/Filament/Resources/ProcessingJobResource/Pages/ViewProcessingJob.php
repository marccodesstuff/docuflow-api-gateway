<?php

namespace App\Filament\Resources\ProcessingJobResource\Pages;

use App\Filament\Resources\ProcessingJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProcessingJob extends ViewRecord
{
    protected static string $resource = ProcessingJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('retry')
                ->label('Retry')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->visible(fn ($record) => in_array($record->status, ['failed', 'cancelled']))
                ->action(fn ($record) => $record->update(['status' => 'pending', 'retry_count' => $record->retry_count + 1])),
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'running', 'paused']))
                ->action(fn ($record) => $record->update(['status' => 'cancelled'])),
        ];
    }
}