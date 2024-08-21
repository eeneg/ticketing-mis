<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers\SubcategoriesRelationManager;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category')
                    ->schema([
                        Forms\Components\Select::make('office_id')
                            ->relationship('office', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Fieldset::make('Tags')
                            ->schema([
                                Forms\Components\Repeater::make('tag')
                                    ->relationship('tags')
                                    ->columnSpanFull()
                                    ->hiddenLabel()
                                    ->grid(3)
                                    ->simple(
                                        Forms\Components\TextInput::make('name')
                                            ->markAsRequired()
                                            ->rule('required')
                                    ),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('office.name')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office_id')
                    ->relationship('office', 'name')
                    ->label('Office')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [
            SubcategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
