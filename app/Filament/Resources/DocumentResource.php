<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->required()
                ->searchable(),

            Forms\Components\Select::make('document_type_id')
                ->relationship('documentType', 'name')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('original_filename')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('storage_path')
                ->required()
                ->maxLength(500),

            Forms\Components\TextInput::make('mime_type')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('size_bytes')
                ->required()
                ->numeric()
                ->minValue(0),

            Forms\Components\Select::make('status')
                ->options([
                    'uploaded' => 'Uploaded',
                    'queued' => 'Queued',
                    'processing' => 'Processing',
                    'review_required' => 'Review Required',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'failed' => 'Failed',
                    'archived' => 'Archived',
                ])
                ->required()
                ->default('uploaded'),

            Forms\Components\KeyValue::make('metadata')
                ->keyLabel('Key')
                ->valueLabel('Value')
                ->addActionLabel('Add Metadata'),
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

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Filename')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->original_filename),

                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Type')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'uploaded',
                        'warning' => 'queued',
                        'info' => 'processing',
                        'orange' => 'review_required',
                        'success' => 'approved',
                        'danger' => ['rejected', 'failed'],
                        'secondary' => 'archived',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('size_bytes')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => match (true) {
                        $state >= 1073741824 => number_format($state / 1073741824, 2) . ' GB',
                        $state >= 1048576 => number_format($state / 1048576, 2) . ' MB',
                        $state >= 1024 => number_format($state / 1024, 2) . ' KB',
                        default => $state . ' B',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'uploaded' => 'Uploaded',
                        'queued' => 'Queued',
                        'processing' => 'Processing',
                        'review_required' => 'Review Required',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'failed' => 'Failed',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant'),

                Tables\Filters\SelectFilter::make('document_type_id')
                    ->relationship('documentType', 'name')
                    ->label('Document Type'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('process')
                    ->label('Process')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn ($record) => in_array($record->status, ['uploaded', 'queued', 'failed']))
                    ->action(fn ($record) => $record->update(['status' => 'queued'])),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('api.documents.download', $record->id), true),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('process')
                    ->label('Process Selected')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(fn ($records) => $records->each->update(['status' => 'queued'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PagesRelationManager::class,
            RelationManagers\ProcessingJobsRelationManager::class,
            RelationManagers\ExtractionResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['tenant', 'documentType']);
    }
}