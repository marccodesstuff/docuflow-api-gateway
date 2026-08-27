<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\Webhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Integrations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('url')
                ->label('Webhook URL')
                ->required()
                ->url()
                ->maxLength(500),

            Forms\Components\TextInput::make('secret')
                ->label('HMAC Secret')
                ->maxLength(200)
                ->helperText('Leave blank to auto-generate'),

            Forms\Components\CheckboxList::make('events')
                ->label('Events')
                ->options([
                    'document_uploaded' => 'Document Uploaded',
                    'document_processing_started' => 'Processing Started',
                    'document_processing_completed' => 'Processing Completed',
                    'document_processing_failed' => 'Processing Failed',
                    'document_review_required' => 'Review Required',
                    'document_approved' => 'Document Approved',
                    'document_rejected' => 'Document Rejected',
                    'extraction_completed' => 'Extraction Completed',
                ])
                ->columns(2)
                ->required(),

            Forms\Components\Toggle::make('active')
                ->label('Active')
                ->default(true),

            Forms\Components\TextInput::make('consecutive_failures')
                ->numeric()
                ->default(0)
                ->disabled(),

            Forms\Components\DateTimePicker::make('last_success_at')
                ->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->url)
                    ->searchable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('events')
                    ->label('Events')
                    ->formatStateUsing(fn ($state) => count($state) . ' events')
                    ->badge(),

                Tables\Columns\TextColumn::make('consecutive_failures')
                    ->label('Failures')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('last_success_at')
                    ->label('Last Success')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-beaker')
                    ->color('primary')
                    ->action(fn ($record) => route('api.webhooks.test', $record->id)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'view' => Pages\ViewWebhook::route('/{record}'),
            'edit' => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['tenant', 'deliveries']);
    }
}