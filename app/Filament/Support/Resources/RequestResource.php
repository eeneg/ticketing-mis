<?php

namespace App\Filament\Support\Resources;

use App\Filament\Actions\AcceptAssignmentAction;
use App\Filament\Actions\RejectAssignmentAction;
use App\Filament\Actions\Tables\AdjustRequestAction;
use App\Filament\Actions\Tables\AmmendRecentActionAction;
use App\Filament\Actions\Tables\ScheduleRequestAction;
use App\Filament\Actions\Tables\UpdateRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Support\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('currentUserAssignee');
            })
            ->columns([
                Tables\Columns\TextColumn::make('requestor.name'),
                Tables\Columns\TextColumn::make('requestor.office.name'),
                Tables\Columns\TextColumn::make('currentUserAssignee.response')
                    ->badge()
                    ->label('Response'),
                Tables\Columns\TextColumn::make('action.status')
                    ->badge()
                    ->label('Status'),
            ])
            ->filters([

            ])
            ->actions([
                UpdateRequestAction::make(),
                Tables\Actions\ViewAction::make()
                    ->modalCancelAction(false)
                    ->color('primary')
                    ->form([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('name')
                                    ->relationship('requestor', 'name')
                                    ->label('Requestor Name'),
                                Select::make('number')
                                    ->relationship('requestor', 'number')
                                    ->label('Number'),
                            ]),
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                Select::make('office')
                                    ->relationship('office', 'name')
                                    ->label('Office Name'),
                                Select::make('address')
                                    ->relationship('office', 'address')
                                    ->label('Address'),
                                Select::make('room')
                                    ->label('Room #')
                                    ->relationship('office', 'room'),
                            ]),
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                Select::make('cat')
                                    ->relationship('category', 'name')
                                    ->label('Category'),
                                Select::make('sub-cat')
                                    ->relationship('subcategory', 'name')
                                    ->label('SubCategory'),
                            ]),
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('priority')
                                    ->placeholder('N/A'),
                                TextInput::make('difficulty')
                                    ->placeholder('N/A'),
                            ]),

                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('target_date')
                                    ->placeholder('N/A'),
                                TextInput::make('target_time')
                                    ->placeholder('N/A'),

                            ]),

                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('availability_from'),
                                TextInput::make('availability_to'),
                            ]),

                        Grid::make()
                            ->columns(1)
                            ->schema([
                                Actions::make([
                                    AcceptAssignmentAction::make(),
                                    RejectAssignmentAction::make(),
                                ])
                                    ->alignCenter(),
                            ]),
                    ]),
                ActionGroup::make([
                    AmmendRecentActionAction::make(),
                    ViewRequestHistoryAction::make(),
                    AdjustRequestAction::make(),
                    ScheduleRequestAction::make(),
                ]),
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
        ];
    }
}
