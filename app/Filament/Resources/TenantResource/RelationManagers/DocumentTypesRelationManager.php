<?php

namespace App\Filament\Resources\TenantResource\RelationManagers;

use App\Models\DocumentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'documentTypes';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'Document Types';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),

            Forms\Components\Textarea::make('description')
                ->rows(3)
                ->maxLength(500),

            Forms\Components\Repeater::make('fields')
                ->label('Fields')
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->required()
                        ->maxLength(100),

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
                ])
                ->columns(2)
                ->collapsible(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fields')
                    ->label('Fields')
                    ->counts('fields')
                    ->badge(),

                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Documents')
                    ->counts('documents')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.document-types.edit', $record)),
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.document-types.view', $record)),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}