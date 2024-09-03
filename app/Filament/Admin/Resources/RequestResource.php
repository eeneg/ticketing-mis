<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Admin\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?int $navigationSort = -100;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor Name'),
                Tables\Columns\TextColumn::make('office.acronym'),
                Tables\Columns\TextColumn::make('subject'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('assignees.response')
                    ->badge()
                    ->label('Response')
                    ->sortable(),
                Tables\Columns\TextColumn::make('action.status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    // ->modalCancelAction(false)
                    ->modalWidth('6xl')
                    ->infolist([
                        ComponentsGrid::make(3)
                            ->schema([

                                Section::make('Assignees')
                                    ->columnSpan(3)
                                    ->schema([
                                        TextEntry::make('Assignees')
                                            ->label('Category :'),
                                        TextEntry::make('subcategory.name')
                                            ->label('Subcategory'),

                                    ]),
                            ]),
                        // Section::make('Time Details')
                        //     ->columnSpan(1)
                        //     ->columns(2)
                        //     ->schema([
                        //         TextEntry::make('created_at')
                        //             ->inlineLabel('Created at'),
                        //         TextEntry::make('updated_at')
                        //             ->inlineLabel('Updated at'),
                        //         TextEntry::make('availability_from')
                        //             ->inlineLabel('Availability from'),
                        //         TextEntry::make('availability_to')
                        //             ->inlineLabel('Availability to'),

                        //     ]),

                    ]),
                // Grid::make()
                //     ->columns(2)
                //     ->schema([
                //         Select::make('name')
                //             ->relationship('requestor', 'name')
                //             ->label('Requestor Name'),
                //         Select::make('number')
                //             ->relationship('requestor', 'number')
                //             ->label('Number'),
                //     ]),
                // Grid::make()
                //     ->columns(3)
                //     ->schema([
                //         Select::make('office')
                //             ->relationship('office', 'acronym')
                //             ->label('Office Name'),
                //         Select::make('address')
                //             ->relationship('office', 'address')
                //             ->label('Address'),
                //         Select::make('room')
                //             ->label('Room #')
                //             ->relationship('office', 'room'),
                //     ]),
                // Grid::make()
                //     ->columns(2)
                //     ->schema([
                //         Select::make('cat')
                //             ->relationship('category', 'name')
                //             ->label('Category'),
                //         Select::make('sub-cat')
                //             ->relationship('subcategory', 'name')
                //             ->label('SubCategory'),
                //     ]),
                // Grid::make()
                //     ->columns(2)
                //     ->schema([
                //         TextInput::make('priority')
                //             ->placeholder('N/A'),
                //         TextInput::make('difficulty')
                //             ->placeholder('N/A'),
                //     ]),

                // Grid::make()
                //     ->columns(2)
                //     ->schema([
                //         TextInput::make('target_date')
                //             ->placeholder('N/A'),
                //         TextInput::make('target_time')
                //             ->placeholder('N/A'),

                //     ]),

                // Grid::make()
                //     ->columns(2)
                //     ->schema([
                //         TextInput::make('availability_from'),
                //         TextInput::make('availability_to'),
                //     ]),

                // ]),
                ViewRequestHistoryAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
        ];
    }
}
