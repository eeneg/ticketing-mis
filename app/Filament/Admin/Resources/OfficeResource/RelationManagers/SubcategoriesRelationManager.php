<?php

namespace App\Filament\Admin\Resources\OfficeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubcategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subcategories';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->editOptionAction(fn ($action) => $action->slideOver())
                    ->editOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->markAsRequired()
                            ->rule('required')
                            ->maxLength(255),
                    ])
                    ->createOptionAction(fn ($action) => $action->slideOver())
                    ->createOptionForm([
                        Forms\Components\Hidden::make('office_id')
                            ->default($this->ownerRecord->getKey()),
                        Forms\Components\TextInput::make('name')
                            ->markAsRequired()
                            ->rule('required')
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('name')
                    ->markAsRequired()
                    ->rule('required')
                    ->maxLength(255),
                Forms\Components\Fieldset::make('Tags')
                    ->schema([
                        Forms\Components\Repeater::make('tag')
                            ->relationship('tags')
                            ->columnSpanFull()
                            ->hiddenLabel()
                            ->grid(3)
                            ->simple(
                                Forms\Components\TextInput::make('name')
                                    ->distinct()
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->maxLength(255)
                            ),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tags.name')
                    ->limit(20),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->recordAction(null);
    }
}
