<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Admin\Resources\RequestResource\Pages;
use App\Models\Request;
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
                    ->label('Requestor Name')
                    ->searchable()
                    ->sortable()
                    ->limit(13),
                Tables\Columns\TextColumn::make('office.acronym')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('assignees.response')
                //     ->searchable()
                //     ->badge()
                //     ->label('Response')
                //     ->sortable(),
                Tables\Columns\TextColumn::make('action.status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('5xl')
                    ->infolist([
                        ComponentsGrid::make(4)
                            ->schema([
                                Section::make('Personal Details')
                                    ->columnSpan(4)
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('requestor.name')
                                            ->label('Name'),
                                        TextEntry::make('requestor.number')
                                            ->prefix('+63 0')
                                            ->label('Phone Number'),
                                        TextEntry::make('requestor.email')
                                            ->label('Email'),

                                    ]),
                                Section::make('Office Details')
                                    ->columnSpan(2)
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('office.acronym')
                                            ->label('Office'),
                                        TextEntry::make('office.room')
                                            ->label('Room Number'),
                                        TextEntry::make('office.address')
                                            ->label('Office address :'),

                                    ]),
                                Section::make('Request Details')
                                    ->columnSpan(2)
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('category.name')
                                            ->label('Category'),
                                        TextEntry::make('subcategory.name')
                                            ->columnSpan(2)
                                            ->label('Subcategory'),
                                        TextEntry::make('tags')
                                            ->label('Tags'),

                                    ]),
                                Section::make('Availability')
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('availability_from')

                                            ->date()
                                            ->columnSpan(2)
                                            ->inlineLabel('Availability from'),
                                        TextEntry::make('availability_to')
                                            ->date()
                                            ->columnSpan(2)
                                            ->inlineLabel('Availability to'),

                                    ]),
                                Section::make('Remarks')
                                    ->columnSpan(4)
                                    ->schema([
                                        TextEntry::make('remarks')
                                            ->inlineLabel('Updated at'),
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
                        //             ->inlineLabel('Availabilisdsty to'),

                        //     ]),

                    ]),
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
