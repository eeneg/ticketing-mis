<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OfficeResource\Pages;
use App\Filament\Admin\Resources\OfficeResource\RelationManagers\CategoriesRelationManager;
use App\Filament\Admin\Resources\OfficeResource\RelationManagers\SubcategoriesRelationManager;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Office')
                    ->columns(3)
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->avatar()
                            ->directory('logos'),
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->unique(ignoreRecord: true)
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('acronym')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('building')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('room')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->circular()
                    ->label('Logo'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('acronym')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->recordUrl(false);
    }

    public static function getRelations(): array
    {
        return [
            CategoriesRelationManager::class,
            SubcategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
