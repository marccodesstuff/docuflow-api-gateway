<?php

namespace App\Filament\Resources\WebhookResource\RelationManagers;

use App\Models\WebhookDelivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';
    protected static ?string $recordTitleAttribute = 'event_type';
    protected static ?string $title = 'Deliveries';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('event_type')
                ->required()
                ->maxLength(50),

            Forms\Components\KeyValue::make('payload')
                ->keyLabel('Key')
                ->valueLabel('Value'),

            Forms\Components\TextInput::make('status_code')
                ->numeric(),

            Forms\Components\Textarea::make('response_body')
                ->rows(5),

            Forms\Components\Textarea::make('error_message')
                ->rows(3),

            Forms\Components\TextInput::make('attempt_number')
                ->numeric(),

            Forms\Components\DateTimePicker::make('delivered_at'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ?? 'Failed')
                    ->color(fn ($state) => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('attempt_number')
                    ->label('Attempt')
                    ->badge(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->error_message),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pending'),
            ])
            ->headerActions([])
            ->actions([])
            ->defaultSort('created_at', 'desc');
    }
}