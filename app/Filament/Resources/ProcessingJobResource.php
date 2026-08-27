<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessingJobResource\Pages;
use App\Models\ProcessingJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProcessingJobResource extends Resource
{
    protected static ?string $model = ProcessingJob::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Processing';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->required()
                ->searchable(),

            Forms\Components\Select::make('document_id')
                ->relationship('document', 'original_filename')
                ->required()
                ->searchable()
                ->getSearchResultsUsing(fn (string $search) => \App\Models\Document::query()
                    ->where('original_filename', 'like', "%{$search}%")
                    ->limit(50)
                    ->pluck('original_filename', 'id')
                    ->toArray()),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'running' => 'Running',
                    'paused' => 'Paused',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                    'cancelled' => 'Cancelled',
                ])
                ->required(),

            Forms\Components\TextInput::make('current_step')
                ->numeric()
                ->default(0),

            Forms\Components\KeyValue::make('context')
                ->keyLabel('Key')
                ->valueLabel('Value'),

            Forms\Components\Textarea::make('error_message')
                ->rows(3),

            Forms\Components\TextInput::make('retry_count')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Job ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('document.original_filename')
                    ->label('Document')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'info' => 'running',
                        'warning' => 'paused',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'secondary' => 'cancelled',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_step')
                    ->label('Step')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->error_message),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'running' => 'Running',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn ($record) => in_array($record->status, ['failed', 'cancelled']))
                    ->action(fn ($record) => $record->update(['status' => 'pending', 'retry_count' => $record->retry_count + 1])),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'running', 'paused']))
                    ->action(fn ($record) => $record->update(['status' => 'cancelled'])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcessingJobs::route('/'),
            'create' => Pages\CreateProcessingJob::route('/create'),
            'view' => Pages\ViewProcessingJob::route('/{record}'),
            'edit' => Pages\EditProcessingJob::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['tenant', 'document', 'steps']);
    }
}