<?php

namespace App\Filament\Resources\ProcessingJobResource\RelationManagers;

use App\Models\JobStep;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Steps';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('step_order')
                ->required()
                ->numeric(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'running' => 'Running',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                    'skipped' => 'Skipped',
                ])
                ->required(),

            Forms\Components\KeyValue::make('input')
                ->keyLabel('Key')
                ->valueLabel('Value'),

            Forms\Components\KeyValue::make('output')
                ->keyLabel('Key')
                ->valueLabel('Value'),

            Forms\Components\Textarea::make('error_message')
                ->rows(3),

            Forms\Components\TextInput::make('retry_count')
                ->numeric()
                ->default(0),

            Forms\Components\DateTimePicker::make('started_at'),

            Forms\Components\DateTimePicker::make('completed_at'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('step_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'info' => 'running',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'secondary' => 'skipped',
                    ])
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
            ->reorderable('step_order')
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('step_order');
    }
}