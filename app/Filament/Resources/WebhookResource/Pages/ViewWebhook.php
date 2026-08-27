<?php

namespace App\Filament\Resources\WebhookResource\Pages;

use App\Filament\Resources\WebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWebhook extends ViewRecord
{
    protected static string $resource = WebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('test')
                ->label('Test Webhook')
                ->icon('heroicon-o-beaker')
                ->color('primary')
                ->url(fn ($record) => route('api.webhooks.test', $record->id), true),
        ];
    }
}