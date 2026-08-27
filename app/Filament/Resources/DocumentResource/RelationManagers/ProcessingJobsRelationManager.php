<?php

namespace App\Filament\Resources\DocumentResource\RelationManagers;

use App\Models\ProcessingJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProcessingJobsRelationManager extends RelationManager
{
    protected static string $relationship = 'processingJobs';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $title = 'Processing Jobs';

    public function form(Form $form): Form
    {
        return $form->schema([
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
                ->numeric(),

            Forms\Components\KeyValue::make('context')
                ->keyLabel('Key')
                ->valueLabel('Value'),

            Forms\Components\Textarea::make('error_message')
                ->rows(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Job ID')
                    ->searchable()
                    ->copyable(),

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
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('view_steps')
                    ->label('View Steps')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn ($record) => route('filament.admin.resources.processing-jobs.view', $record->id)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}