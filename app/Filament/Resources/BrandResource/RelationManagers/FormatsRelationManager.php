<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BrandFormat;
use App\Models\FormatField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager as FilamentRelationManager;

class FormatsRelationManager extends FilamentRelationManager
{
    protected static string $relationship = 'formats';

    public function form(Form $form): Form
    {
        $format = $form->model;

        // Fetch all fields associated with this format
        $fieldsForThisFormat = FormatField::where('format_id', $format->id)->get();

        // Create an array to hold the dynamic form components
        $formComponents = [];

        // Iterate over each field and create a corresponding form component
        foreach ($fieldsForThisFormat as $field) {
            switch ($field->type) {
                case 'string':
                    $component = Forms\Components\TextInput::make($field->id)
                        ->label(ucfirst($field->name))
                        ->required($field->required)
                        ->default('Hello World');
                    break;
                    // Add other cases for different types
                default:
                    $component = null;
            }

            if ($component) {
                $formComponents[] = $component;
            }
        }

        // Construct the form schema with the dynamic components
        return $form->schema($formComponents);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Model $record, array $data): Model {
                        $brandFormat = BrandFormat::whereBrandId($record->brand_id)->whereFormatId($record->format_id)->first();

                        $brandFormat->fields = json_encode($data);

                        $brandFormat->save();

                        return $record;
                    })->mutateRecordDataUsing(function (array $data): array {
                        $brandFormat = BrandFormat::whereBrandId($data['brand_id'])->whereFormatId($data['format_id'])->first();

                        $brandFormatFields = json_decode($brandFormat->fields, true);

                        if (!$brandFormatFields) {
                            $brandFormatFields = [];
                        }

                        foreach ($brandFormatFields as $key => $value) {
                            $data[$key] = $value;
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
