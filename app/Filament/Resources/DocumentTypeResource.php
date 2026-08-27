<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentTypeResource\Pages;
use App\Models\DocumentType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentTypeResource extends Resource
{
    protected static ?string $model = DocumentType::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),

            Forms\Components\Textarea::make('description')
                ->rows(3)
                ->maxLength(500),

            Forms\Components\Repeater::make('fields')
                ->label('Field Definitions')
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->required()
                        ->maxLength(100)
                        ->helperText('Unique field identifier (e.g., invoice_number)'),

                    Forms\Components\TextInput::make('label')
                        ->required()
                        ->maxLength(200)
                        ->helperText('Display label (e.g., Invoice Number)'),

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
                        ->rows(2)
                        ->maxLength(500),

                    Forms\Components\TextInput::make('regex_pattern')
                        ->maxLength(500)
                        ->helperText('Regex pattern for validation'),

                    Forms\Components\KeyValue::make('enum_values')
                        ->keyLabel('Value')
                        ->valueLabel('Label')
                        ->visible(fn (Forms\Get $get) => $get('type') === 'array'),
                ])
                ->columns(2)
                ->itemLabel(fn (array $state): string => $state['label'] ?? $state['key'] ?? 'Field')
                ->collapsible(),

            Forms\Components\Repeater::make('validation_rules')
                ->label('Validation Rules')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'field' => 'Field Rule',
                            'cross_field' => 'Cross-Field Rule',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('field_key')
                        ->label('Field Key')
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('type') === 'field'),

                    Forms\Components\TextInput::make('rule')
                        ->label('Pattern/Rule')
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('type') === 'field'),

                    Forms\Components\TextInput::make('expression')
                        ->label('Expression')
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('type') === 'cross_field'),

                    Forms\Components\TextInput::make('error_message')
                        ->label('Error Message')
                        ->required()
                        ->visible(fn (Forms\Get $get) => $get('type') === 'cross_field'),

                    Forms\Components\TagsInput::make('affected_fields')
                        ->label('Affected Fields')
                        ->visible(fn (Forms\Get $get) => $get('type') === 'cross_field'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Routing Rules')
                ->schema([
                    Forms\Components\TextInput::make('routing_rules.default_assignee_group')
                        ->label('Default Assignee Group')
                        ->maxLength(100),

                    Forms\Components\Repeater::make('routing_rules.field_based_routing')
                        ->label('Field-Based Routing')
                        ->schema([
                            Forms\Components\TextInput::make('field_value')
                                ->required(),
                            Forms\Components\TextInput::make('assignee_group')
                                ->required(),
                        ])
                        ->columns(2),

                    Forms\Components\TextInput::make('routing_rules.sla_hours')
                        ->label('SLA (Hours)')
                        ->numeric()
                        ->default(24),

                    Forms\Components\Toggle::make('routing_rules.auto_approve_threshold_enabled')
                        ->label('Auto-Approve Enabled')
                        ->default(false),

                    Forms\Components\TextInput::make('routing_rules.auto_approve_confidence')
                        ->label('Auto-Approve Confidence')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->maxValue(1)
                        ->default(0.95),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
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
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->label('Tenant'),
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
        return [
            RelationManagers\FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'view' => Pages\ViewDocumentType::route('/{record}'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['tenant', 'fields', 'documents']);
    }
}