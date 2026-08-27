<?php

namespace App\Filament\Resources\DocumentResource\RelationManagers;

use App\Models\ExtractionResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExtractionResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'extractionResults';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Extraction Results';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options([
                    'success' => 'Success',
                    'partial' => 'Partial',
                    'failed' => 'Failed',
                    'review_needed' => 'Review Needed',
                ])
                ->required(),

            Forms\Components\TextInput::make('overall_confidence')
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->maxValue(1),

            Forms\Components\TextInput::make('model_version')
                ->maxLength(100),

            Forms\Components\KeyValue::make('fields')
                ->keyLabel('Field Key')
                ->valueLabel('Value'),

            Forms\Components\KeyValue::make('tables')
                ->keyLabel('Table ID')
                ->valueLabel('Data'),

            Forms\Components\KeyValue::make('issues')
                ->keyLabel('Field')
                ->valueLabel('Issue'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Result ID')
                    ->searchable()
                    ->copyable(),

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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Extracted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('view_fields')
                    ->label('View Fields')
                    ->icon('heroicon-o-key')
                    ->url(fn ($record) => route('filament.admin.resources.extraction-results.view', $record->id)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}