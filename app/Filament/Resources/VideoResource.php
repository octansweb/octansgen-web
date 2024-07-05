<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Video;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\VideoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VideoResource\RelationManagers;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required(),
                Forms\Components\Select::make('format_id')
                    ->relationship('format', 'name')
                    ->required(),
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('file_path')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('instagram_post_description')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('youtube_description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('format.name')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('file_path')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereUserId(auth()->id());
            })
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('brand.name')
                    ->label('Brand'),
                TextEntry::make('format.name')
                    ->label('Format'),

                Grid::make()->schema([
                    TextEntry::make('file_path')
                        ->label('File Path')
                        ->url(fn ($record) => url($record->file_path))
                        ->openUrlInNewTab()
                        ->tooltip('Click to open the file')
                        ->formatStateUsing(fn ($state) => Str::limit($state, 30)),
                ]),

                Grid::make()->schema([
                    TextEntry::make('script')
                        ->label('Voiceover script')
                        ->copyable()
                        ->columnSpan('full'),
                ]),
                Grid::make()->schema([
                    TextEntry::make('instagram_description')
                        ->label('Instagram Post Description')
                        ->copyable()
                        ->columnSpan('full'),
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
            'index' => Pages\ListVideos::route('/'),
            // 'create' => Pages\CreateVideo::route('/create'),
            // 'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }
}
