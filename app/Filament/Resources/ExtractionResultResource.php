<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtractionResultResource\Pages;
use App\Models\ExtractionResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExtractionResultResource extends Resource
{
    protected static ?string $model = ExtractionResult::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Processing';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('job_id')
                ->relationship('job', 'id')
                ->required()
                ->searchable(),

            Forms\Components\Select::make('document_id')
                ->relationship('document', 'original_filename')
                ->required()
                ->searchable(),

            Forms\Components\Select::make('status')
                ->options([
                    'success' => 'Success',
                    'partial' => 'Partial',
                    'failed' => 'Failed',
                    'review_needed' => 'Review Needed',
                ])
                ->required(),

            Forms\Components\KeyValue::make('fields')
                ->keyLabel('Field Key')
                ->valueLabel('Value'),

            Forms\Components\KeyValue::make('tables')
                ->keyLabel('Table ID')
                ->valueLabel('Data'),

            Forms\Components\TextInput::make('overall_confidence')
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->maxValue(1),

            Forms\Components\TextInput::make('model_version')
                ->maxLength(100),

            Forms\Components\KeyValue::make('issues')
                ->keyLabel('Field')
                ->valueLabel('Issue'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Result ID')
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
                        'success' => 'success',
                        'warning' => 'partial',
                        'danger' => 'failed',
                        'orange' => 'review_needed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_confidence')
                    ->label('Confidence')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state * 100, 1) . '%' : 'N/A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('model_version')
                    ->label('Model')
                    ->limit(30),

                Tables\Columns\TextColumn::make('job.status')
                    ->label('Job Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Extracted')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'partial' => 'Partial',
                        'failed' => 'Failed',
                        'review_needed' => 'Review Needed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExtractionResults::route('/'),
            'create' => Pages\CreateExtractionResult::route('/create'),
            'view' => Pages\ViewExtractionResult::route('/{record}'),
            'edit' => Pages\EditExtractionResult::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['job', 'document']);
    }
}