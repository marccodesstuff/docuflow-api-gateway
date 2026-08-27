<?php

namespace App\Filament\Resources\DocumentResource\RelationManagers;

use App\Models\DocumentPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PagesRelationManager extends RelationManager
{
    protected static string $relationship = 'pages';
    protected static ?string $recordTitleAttribute = 'page_number';
    protected static ?string $title = 'Pages';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('page_number')
                ->required()
                ->numeric(),

            Forms\Components\TextInput::make('storage_path')
                ->required()
                ->maxLength(500),

            Forms\Components\TextInput::make('width_px')
                ->numeric(),

            Forms\Components\TextInput::make('height_px')
                ->numeric(),

            Forms\Components\KeyValue::make('elements')
                ->keyLabel('Key')
                ->valueLabel('Value'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_number')
                    ->label('Page')
                    ->sortable(),

                Tables\Columns\TextColumn::make('storage_path')
                    ->label('Path')
                    ->limit(50),

                Tables\Columns\TextColumn::make('width_px')
                    ->label('Width')
                    ->suffix('px')
                    ->sortable(),

                Tables\Columns\TextColumn::make('height_px')
                    ->label('Height')
                    ->suffix('px')
                    ->sortable(),
            ])
            ->headerActions([])
            ->actions([])
            ->defaultSort('page_number');
    }
}