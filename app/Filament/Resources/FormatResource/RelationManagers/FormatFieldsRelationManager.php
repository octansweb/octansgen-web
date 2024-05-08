<?php

namespace App\Filament\Resources\FormatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FormatFieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'formatFields';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('format_id')
                //     ->relationship('format', 'name')
                //     ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                // Add a select component for the type field
                Forms\Components\Select::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('required')
                    ->required(),
                Forms\Components\TextInput::make('default_value')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
