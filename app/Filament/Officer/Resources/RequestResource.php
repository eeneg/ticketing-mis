<?php

namespace App\Filament\Officer\Resources;

use App\Filament\Officer\Resources\RequestResource\Pages;
use App\Models\Action as ActionModel;
use App\Models\Request;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            ->columns([
                Tables\Columns\TextColumn::make('office.name')->label('Office'),
                Tables\Columns\TextColumn::make('actions.remarks')
                    ->label('Remarks')
                    ->html(),
                Tables\Columns\TextColumn::make('actions.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'Deleted' => 'danger',
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
                        TextInput::make('dfdafdf')
                            ->placeholder('Hello'),
                    ])
                    ->color('success'),

                Tables\Actions\EditAction::make()
                    ->color('info'),
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
                                //     ->action(function ($record, $data) {
                    //         $action = ActionModel::where('request_id', $record->id)
                    //             ->where('user_id', Auth::id())
                    //             ->update(['remarks' => $data['remarks'], 'status' => 'Responded']);

                    //     })
                        ->action(function ($data, $record) {
                            // $validator = Validator::make($data, [
                            //     'user_ids.*' => [
                            //         Rule::unique('assignees', 'user_id')
                            //             ->where(function ($query) use ($record) {
                            //                 return $query->where('request_id', $record->id);
                            //             }),
                            //     ],
                            // ]);

                            // if ($validator->fails()) {
                            //     Notification::make()
                            //         ->title('Error Duplicate Entry')
                            //         ->danger()
                            //         ->send();

                            //     return;
                            // }

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
                            // dd($record);
                            Notification::make()
                                ->title('Request Assigned Successfully')
                                ->success()
                                ->send();
                            $action = ActionModel::where('request_id', $record->id)
                                ->where('user_id', Auth::id())
                                ->update(['remarks' => $data['remarks'], 'status' => 'Assigned']);
                            // $record->update(['remarks' => $data['remarks']]);
                            $record->update(['priority' => $data['priority'], 'status' => 'Assigned']);
                        }),
                    Action::make('Reject')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->action(function ($record) {
                            $action = ActionModel::where('request_id', $record->id)
                                ->where('user_id', Auth::id())
                                ->update(['status' => 'Rejected']);
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
            'create' => Pages\CreateRequest::route('/create'),
            'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }
}
