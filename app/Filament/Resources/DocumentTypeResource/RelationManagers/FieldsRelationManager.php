<?php

namespace App\Filament\Resources\DocumentTypeResource\RelationManagers;

use App\Models\FieldDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';
    protected static ?string $recordTitleAttribute = 'label';
    protected static ?string $title = 'Fields';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('label')
                ->required()
                ->maxLength(200),

            Forms\Components\Select::make('type')
                ->options([
                    'string' => 'String',
                    'number' => 'Number',
                    'boolean' => 'Boolean',
                    'date' => 'Date',
                    'datetime' => 'DateTime',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'currency' => 'Currency',
                    'table' => 'Table',
                    'object' => 'Object',
                    'array' => 'Array',
                ])
                ->required()
                ->default('string'),

            Forms\Components\Toggle::make('required')
                ->default(false),

            Forms\Components\Textarea::make('description')
                ->rows(2),

            Forms\Components\TextInput::make('regex_pattern')
                ->maxLength(500),

            Forms\Components\KeyValue::make('enum_values')
                ->keyLabel('Value')
                ->valueLabel('Label')
                ->visible(fn (Forms\Get $get) => $get('type') === 'array'),

            Forms\Components\Select::make('parent_id')
                ->label('Parent Field')
                ->options(fn () => $this->getOwnerRecord()->fields()->pluck('label', 'id'))
                ->searchable()
                ->preload(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('label')
                    ->label('Label')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'number', 'currency' => 'green',
                        'boolean' => 'orange',
                        'date', 'datetime' => 'blue',
                        'email', 'phone' => 'purple',
                        'table', 'object', 'array' => 'indigo',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\TextColumn::make('parent.label')
                    ->label('Parent')
                    ->placeholder('—'),
            ])
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}