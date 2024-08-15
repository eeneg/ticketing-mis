<?php

namespace App\Filament\Support\Resources;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Filament\Support\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Columns\TextColumn::make('currentUserAssignee.response')
                    ->label('Response')
                    ->prefix('• '),
                Tables\Columns\TextColumn::make('requestor.name'),
                Tables\Columns\TextColumn::make('actions.status')
                    ->label('Status'),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\Action::make('update')
                    ->color('info')
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options(RequestStatus::class)
                            ->native(false),
                        RichEditor::make('remarks'),
                    ])

                    ->action(function (array $data, $record) {

                        $record->action()->updateOrCreate([
                            'user_id' => Auth::id(),
                            'actions.request_id' => $record->id,
                        ], [
                            'status' => $data['status'],
                            'time' => now(),
                            'remarks' => $data['remarks'],
                        ]);
                        Notification::make()
                            ->title('Submitted Successfully!')
                            ->success()
                            ->send();

                    }),
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
                                    ->relationship('requestor', 'number'),
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
                                    ->label('Sub-Category'),
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
                                    Action::make('accept')
                                        ->button()
                                        ->icon('heroicon-c-check-circle')
                                        ->color('success')
                                        ->close()
                                        ->disabled(function ($record) {
                                            return $record->currentUserAssignee->responded_at?->addMinutes(15)->lt(now());
                                        })
                                        ->action(function ($record) {
                                            if ($record->currentUserAssignee->responded_at?->addMinutes(15)->lt(now())) {
                                                $this->close();
                                                Notification::make()
                                                    ->title('No activity for 15 minutes')
                                                    ->Warning()
                                                    ->send();

                                                return;
                                            }
                                            $record->currentUserAssignee()->updateOrCreate([
                                                'user_id' => Auth::id(),
                                                'assignees.request_id' => $record->id,
                                            ], [
                                                'response' => UserAssignmentResponse::ACCEPTED,
                                                'responded_at' => $record->currentUserAssignee->responded->at ?? now(),
                                            ]);
                                            Notification::make()
                                                ->title('Accepted Successfully!')
                                                ->success()
                                                ->send();
                                        }),

                                    Action::make('reject')
                                        ->button()
                                        ->icon('heroicon-c-x-circle')
                                        ->color('danger')
                                        ->close()
                                        ->action(function ($record) {
                                            if ($record->currentUserAssignee->responded_at?->addMinutes(15)->lt(now())) {
                                                Notification::make()
                                                    ->title('No activity for 15 minutes')
                                                    ->Warning()
                                                    ->send();

                                                return;
                                            }
                                            $record->currentUserAssignee()->updateOrCreate([
                                                'user_id' => Auth::id(),
                                                'assignees.request_id' => $record->id,
                                            ], [
                                                'response' => UserAssignmentResponse::REJECTED,
                                                'responded_at' => $record->currentUserAssignee->responded->at ?? now(),
                                            ]);
                                            Notification::make()
                                                ->title('Rejected Successfully!')
                                                ->danger()
                                                ->send();
                                        })
                                        ->disabled(function ($record) {
                                            if ($record->currentUserAssignee->responded_at == null) {
                                                return;
                                            }

                                            return $record->currentUserAssignee->responded_at->addMinutes(15)->lt(now());
                                        }),
                                ])
                                    ->alignCenter(),
                            ]),
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
            'create' => Pages\CreateRequest::route('/create'),
        ];
    }
}
