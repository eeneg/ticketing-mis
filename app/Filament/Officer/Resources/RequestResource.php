<?php

namespace App\Filament\Officer\Resources;

use App\Filament\Officer\Resources\RequestResource\Pages;
use App\Models\Action as ActionModel;
use App\Models\Request;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
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
                // TextInput::make('id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('office.name')->label('Office'),
                // Tables\Columns\TextColumn::make('id')->label('Request ID'),
                Tables\Columns\TextColumn::make('action.remarks')
                    ->label('Remarks')
                    ->html(),
                Tables\Columns\TextColumn::make('action.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'Deleted' => 'danger',
                        'Reassigned' => 'info',
                        'Responded' => 'info',
                        'published' => 'success',
                        'Rejected' => 'danger',
                        default => 'primary'
                    }),

                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        1 => 'gray',
                        2 => 'info',
                        3 => 'success',
                        4 => 'warning',
                        5 => 'danger',
                        default => 'success'
                    }),
                Tables\Columns\TextColumn::make('subcategory.name')->label('Subcategory'),
                Tables\Columns\TextColumn::make('requestor.name')->label('Requestor'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->action(function ($data) {})
                    ->form([
                        Grid::make('category')
                            ->columns(4)
                            ->schema([
                                Select::make('cat')
                                    ->columnSpan(2)
                                    ->label('Category')
                                    ->relationship('category', 'name'),
                                Select::make('Subcat')
                                    ->columnSpan(2)
                                    ->label('SubCategory')
                                    ->relationship('subcategory', 'name'),
                                TextInput::make('remarks')
                                    ->columnSpan(4),
                                Select::make('Office')
                                    ->columnSpan(2)
                                    ->relationship('office', 'name'),
                                Select::make('Office')
                                    ->columnSpan(2)
                                    ->relationship('office', 'building'),
                                Select::make('ActionModel')
                                    ->label('Status')
                                    ->columnSpan(2)
                                    ->relationship('action', 'status'),
                                TextInput::make('priority')
                                    ->columnSpan(2),
                                TextInput::make('availability_from')
                                    ->columnSpan(2),
                                TextInput::make('availability_to')
                                    ->columnSpan(2),
                            ]),

                    ])
                    ->color('success'),

                // ActionGroup::make([
                //     Action::make('Reassign')
                //         ->color('primary')
                //         ->icon('heroicon-s-pencil-square')
                //         ->form([
                //             Forms\Components\Select::make('priority')
                //                 ->label('New Priority')
                //                 ->options([
                //                     '1' => '1',
                //                     '2' => '2',
                //                     '3' => '3',
                //                     '4' => '4',
                //                     '5' => '5',
                //                 ])
                //                 ->required(),
                //             RichEditor::make('remarks')
                //                 ->label('New Remarks')
                //                 ->required(),
                //             Forms\Components\Select::make('user_ids')
                //                 ->label('Assignees')
                //                 ->default('Hello')
                //                 ->options(User::query()->where('role', 'support')->pluck('name', 'id'))
                //                 ->multiple(),
                //         ])
                //         ->action(function ($data, $record) {

                //             $userIds = $data['user_ids'] ?? [];

                //             $record->assignees()->createMany(
                //                 collect($userIds)->map(function ($id) use ($record) {
                //                     return [
                //                         'assigner_id' => Auth::id(),
                //                         'request_id' => $record->id,
                //                         'user_id' => $id,
                //                         'response' => 'pending',
                //                     ];
                //                 })
                //             );
                //             dd(Auth::id('name'));
                //             $record->action()->create([

                //                 'request_id' => $record->id,
                //                 'user_id' => Auth::id(),
                //                 'status' => 'Reassigned',
                //                 'remarks' => $record['remarks'],
                //                 'time' => now(),
                //             ]);
                //             Notification::make()
                //                 ->title('Request Reassigned Successfully')
                //                 ->success()
                //                 ->send();
                //             $action = ActionModel::where('request_id', $record->id)
                //                 ->where('user_id', Auth::id())
                //                 ->update(['remarks' => $data['remarks'], 'status' => 'Reassigned']);
                //             $record->update(['priority' => $data['priority'], 'status' => 'Reassigned']);
                //         }),
                //     Action::make('Delete')
                //         ->icon('heroicon-o-trash')
                //         ->requiresConfirmation()
                //         ->color('danger'),
                // ]),
                ActionGroup::make([
                    Action::make('Accept')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('priority')
                                ->options([
                                    '1' => '1',
                                    '2' => '2',
                                    '3' => '3',
                                    '4' => '4',
                                    '5' => '5',
                                ])
                                ->required(),
                            Forms\Components\RichEditor::make('remarks')
                                ->label('Remarks')
                                ->required(),

                            Forms\Components\Select::make('user_ids')
                                ->label('Assignees')
                                ->options(User::query()->where('role', 'support')->pluck('name', 'id'))
                                ->multiple(),
                        ])
                        ->closeModalByClickingAway(false)
                        ->action(function ($data, $record) {

                            $userIds = $data['user_ids'] ?? [];

                            $record->assignees()->createMany(
                                collect($userIds)->map(function ($id) use ($record) {
                                    return [
                                        'assigner_id' => Auth::id(),
                                        'request_id' => $record->id,
                                        'user_id' => $id,
                                        'response' => 'pending',
                                    ];
                                })
                            );
                            $record->action()->create([
                                'request_id' => $record->id,
                                'user_id' => $record->id,
                                'status' => 'Assigned',
                                'remarks' => $record['remarks'],
                                'time' => now(),
                            ]);
                            Notification::make()
                                ->title('Request Assigned Successfully')
                                ->success()
                                ->send();
                            $action = ActionModel::where('request_id', $record->id)
                                ->where('user_id', Auth::id())
                                ->update(['remarks' => $data['remarks'], 'status' => 'Assigned']);
                            $record->update(['priority' => $data['priority'], 'status' => 'Assigned']);
                        }),

                    ViewAction::make('viewactions')
                        ->color('primary')
                        ->label('View Logs')
                        ->action(fn (Request $record) => $record->viewactions())
                        ->icon('heroicon-s-folder')
                        ->slideOver()
                        ->modalContent(function (Request $record) {
                            $relatedRecords = $record->actions()->get();

                            return view('filament.officer.resources.request-resource.pages.actions.viewactions', [
                                'records' => $relatedRecords,
                            ]);
                        }),

                    Action::make('Reject')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->action(function ($record) {
                            $record->action()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => 'Rejected',
                                'remarks' => $record['remarks'],
                                'time' => now(),
                            ]);

                        }),

                ]),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
        ];
    }
}
