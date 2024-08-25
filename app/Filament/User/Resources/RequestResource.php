<?php

namespace App\Filament\User\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\Table\ViewActionsAction;
use App\Filament\User\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('office_id')
                    ->relationship('office', 'name')
                    ->columnSpan(2)
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null) | $set('subcategory_id', null)),
                Forms\Components\Select::make('category_id')
                    ->required()
                    ->relationship('category', 'name', fn (Builder $query, callable $get) => $query->where('office_id', $get('office_id')))
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn (callable $set) => $set('subcategory_id', null)),
                Forms\Components\Select::make('subcategory_id')
                    ->label('Subcategory')
                    ->relationship('subcategory', 'name', fn (Builder $query, callable $get) => $query->where('category_id', $get('category_id')))
                    ->preload()
                    ->searchable(),
                Forms\Components\RichEditor::make('remarks')
                    ->columnSpan(2)
                    ->label('Remarks')
                    ->placeholder('Describe the issue'),
                Forms\Components\DateTimePicker::make('availability_from')
                    ->placeholder('24:00')
                    ->displayFormat('Y-m-d')
                    ->seconds(false),
                Forms\Components\DateTimePicker::make('availability_to')
                    ->after('availability_from')
                    ->placeholder('24:00')
                    ->displayFormat('Y-m-d')
                    ->seconds(false),
                Forms\Components\FileUpload::make('attachment_id')
                    ->columnSpan(2)
                    ->multiple()
                    ->directory('attachments')
                    ->label('Attachments')
                    ->preserveFilenames(),
                // ->action(function ($record, $data) {
                //     $files = $record['attachment_id'] ?? [];
                //     $record->attachments()->createMany(
                //         collect($files)->map(function ($attachment_id)use ($record) {
                //             return [
                //                 'file' => ,
                //                 'attachable_type' => ,
                //                 'attachable_id' => ,
                //             ];
                //         })
                //     );
                // },

                // ),

                Forms\Components\Hidden::make('requestor_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('requestor_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Office'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('actions.status')
                    ->badge(RequestStatus::class)
                    ->label('Status'),
                Tables\Columns\TextColumn::make('subcategory.name')
                    ->label('Subcategory'),
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published Date'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        // dd($record);
                        $latestAction = $record->actions()->latest()->first();
                        $latestActionStatus = $latestAction?->status;

                        return $latestActionStatus == '' ||
                               $latestActionStatus == RequestStatus::RETRACTED;
                    }),
                Tables\Actions\ViewAction::make()
                    ->color('success'),
                ActionGroup::make([
                    Action::make('Publish')
                        ->color('success')
                        ->label(function ($record) {
                            $isPublished = $record->actions()->latest()->first();
                            $isPublishedAction = $isPublished?->status;
                            if ($isPublishedAction == RequestStatus::RETRACTED) {
                                return 'Republish';
                            }

                            return 'Publish';

                        })
                        ->icon('heroicon-c-newspaper')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update([
                                'office_id' => $record['office_id'],
                                'category_id' => $record['category_id'],
                                'subcategory_id' => $record['subcategory_id'],
                                'remarks' => $record['remarks'],
                                'availability_from' => $record['availability_From'],
                                'availability_to' => $record['availability_to'],
                                'published_at' => now(),
                            ]);
                            $record->actions()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::PUBLISHED,
                                'remarks' => $record['remarks'],
                                'time' => now(),
                            ]);
                            Notification::make()
                                ->title('Request Published Successfully')
                                ->success()
                                ->send();

                        })
                        ->visible(function ($record) {
                            $isPublished = $record->actions()->latest()->first();
                            $isPublishedAction = $isPublished?->status;

                            return $isPublishedAction == RequestStatus::RETRACTED ||
                                   $isPublishedAction == '';
                        }),
                    Action::make('Retract')
                        ->icon('heroicon-s-archive-box-x-mark')
                        ->color('danger')
                        ->action(function ($record) {
                            $record->actions()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::RETRACTED,
                                'time' => now(),
                            ]);
                        })
                        ->visible(function ($record) {
                            $isPublished = $record->actions()->latest()->first();
                            $isPublishedAction = $isPublished?->status;

                            return $isPublishedAction == RequestStatus::PUBLISHED;
                            Notification::make()
                                ->title('Request Retracted Successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('CloseTicket')
                        ->icon('heroicon-s-lock-closed')
                        ->requiresConfirmation()
                        ->visible(false)
                        ->color('danger'),
                    ViewActionsAction::make(),
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
