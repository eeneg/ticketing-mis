<?php

namespace App\Filament\Support\Resources;

use App\Enums\RequestStatus;
use App\Enums\UserAssignmentResponse;
use App\Filament\Support\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                Tables\Actions\Action::make('update')
                    ->color('info')
                    ->button()
                    ->disabled(function ($record) {
                        return $record->currentUserAssignee->response->name == 'PENDING';

                    })
                    ->form([
                        Select::make('status')
                            ->options([
                                RequestStatus::COMPLETED->value => RequestStatus::COMPLETED->getLabel(),
                                RequestStatus::SUSPENDED->value => RequestStatus::SUSPENDED->getLabel(),
                                RequestStatus::CANCELLED->value => RequestStatus::CANCELLED->getLabel()
                            ])
                            ->native(false),
                        RichEditor::make('remarks'),
                        FileUpload::make('attachments')
                            ->directory('attachments')
                            ->multiple()
                            ->panelLayout('grid'),

                    ])
                    ->action(function (array $data, $record) { dd($record);
                        $record->action()->create([
                            'user_id' => Auth::id(),
                            'actions.request_id' => $record->id,
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
                                    Action::make('accept')
                                        ->button()
                                        ->icon('heroicon-c-check-circle')
                                        ->color('success')
                                        ->close()
                                        ->hidden(function ($record) {
                                            return $record->currentUserAssignee->responded_at?->addMinutes(15)->lt(now());
                                        })
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
                                        ->hidden(function ($record) {
                                            if ($record->currentUserAssignee->responded_at == null) {
                                                return;
                                            }
                                            return $record->currentUserAssignee->responded_at->addMinutes(15)->lt(now());
                                        }),
                                ])
                                    ->alignCenter(),
                            ]),
                    ]),
                ActionGroup::make([
                    ViewAction::make('viewactions')
                        ->color('primary')
                        ->icon('heroicon-s-folder')
                        ->slideOver()
                        ->modalContent(function (Request $record) {
                            $relatedRecords = $record->actions()->get();

                            return view('filament.officer.resources.request-resource.pages.actions.viewactions', [
                                'records' => $relatedRecords,
                            ]);
                        }),
                    Tables\Actions\Action::make('difficulty')
                        ->label('Set Difficulty')
                        ->icon('heroicon-s-adjustments-vertical')
                        ->action(function ($record,$data) {
                            $from = $record->difficulty;

                            $record->update(['difficulty'=>$data['diff']]);

                            $record->action()->create([
                                'user_id' => Auth::id(),
                                'actions.request_id' => $record->id,
                                'status' => RequestStatus::ADJUSTED->value,
                                'time' => now(),
                                'remarks' => 'Difficulty' . ($from ? ' from ' . $from: '') .  ' to ' . $data['diff'],
                            ]);
                        })
                        ->form([
                            Select::make('diff')
                                ->label('Difficulty Level')
                                ->options([
                                    '1'=>'1',
                                    '2'=>'2',
                                    '3'=>'3',
                                    '4'=>'4',
                                    '5'=>'5'
                                ])
                                ]),
                        Tables\Actions\Action::make('target-date-time')
                            ->icon('heroicon-s-clock')
                            ->label('Set target Date and Time')
                            ->modalWidth(MaxWidth::Large)
                            ->form([
                                DatePicker::make('target_date')
                                    ->required()
                                    ->minDate(fn($record) => $record->availability_from)
                                    ->maxDate(fn($record) => $record->availability_to),
                                TimePicker::make('target_time')
                                    ->required()
                                    ->seconds(false)
                                    ->placeholder('12:00')
                                    ->rule(fn () => function ($a, $v, $f) {
                                        if($v < '08:00' || $v > '17:00'){
                                            $f('Invalid time');
                                        }
                                    })
                            ])
                            ->action(function ($record,$data) {
                                $from = $record->target_date .' '.  $record->target_time ;

                                $record->update($data);

                                $record->action()->create([
                                    'user_id' => Auth::id(),
                                    'actions.request_id' => $record->id,
                                    'status' => RequestStatus::SCHEDULED->value,
                                    'time' => now(),
                                    'remarks' => 'Scheduled' . ($from ? ' from ' . $from: '') .  ' to ' . $data['target_date'].' '.$data['target_time'],
                                ]);
                                Notification::make()
                                                ->title('Scheduled Successfully!')
                                                ->success()
                                                ->send();
                            })

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
