<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormatFieldResource\Pages;
use App\Filament\Resources\FormatFieldResource\RelationManagers;
use App\Models\FormatField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FormatFieldResource extends Resource
{
    protected static ?string $model = FormatField::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('format_id')
                    ->relationship('format', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                // Add a select component for the type field
                Forms\Components\Select::make('type')
                    ->options([
                        'string' => 'String',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('required')
                    ->required(),
                Forms\Components\TextInput::make('default_value')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('format.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('required')
                    ->boolean(),
                Tables\Columns\TextColumn::make('default_value')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormatFields::route('/'),
            'create' => Pages\CreateFormatField::route('/create'),
            'edit' => Pages\EditFormatField::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return false;
    }
}
