<?php

namespace App\Filament\Support\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\AcceptAssignmentAction;
use App\Filament\Actions\RejectAssignmentAction;
use App\Filament\Actions\Tables\AdjustRequestAction;
use App\Filament\Actions\Tables\AmmendRecentActionAction;
use App\Filament\Actions\Tables\DenyCompletedAction;
use App\Filament\Actions\Tables\ScheduleRequestAction;
use App\Filament\Actions\Tables\StartedRequestAction;
use App\Filament\Actions\Tables\UpdateRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Support\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
                Tables\Columns\TextColumn::make('requestor.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestor.office.acronym')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currentUserAssignee.response')
                    ->badge()
                    ->label('Response')
                    ->sortable(),
                Tables\Columns\TextColumn::make('action.status')
                    ->badge()
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                UpdateRequestAction::make(),
                Tables\Actions\ViewAction::make()
                    ->modalCancelAction(false)
                    ->modalWidth('7xl')
                    ->infolist([
                        ComponentsGrid::make(12)
                            ->schema([
                                Group::make([
                                    Section::make('Personal Details')
                                        ->columnSpan(8)
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
                                        ->columnSpan(8)
                                        ->columns(3)
                                        ->schema([
                                            TextEntry::make('office.acronym')
                                                ->label('Office'),
                                            TextEntry::make('office.room')
                                                ->label('Room Number'),
                                            TextEntry::make('office.address')
                                                ->label('Office address :'),

                                        ]),

                                ])->columnSpan(8),

                                Group::make([
                                    Section::make('Availability')
                                        ->columnSpan(4)
                                        ->columns(2)
                                        ->schema([
                                            TextEntry::make('availability_from')
                                                ->columnSpan(1)
                                                ->date()
                                                ->label('Availability from'),
                                            TextEntry::make('availability_to')
                                                ->columnSpan(1)
                                                ->date()
                                                ->label('Availability to'),
                                        ]),
                                    Section::make('Assignees')
                                        ->columnSpan(4)
                                        ->schema([
                                            TextEntry::make('')
                                                ->label(false)
                                                ->placeholder(fn ($record) => implode(', ', $record->assignees->pluck('name')->toArray()))
                                                ->inLinelabel(false),
                                        ]),

                                ])->columnSpan(4),
                                Group::make([

                                    Section::make('Request Details')
                                        ->columnSpan(5)
                                        ->columns(2)
                                        ->schema([
                                            TextEntry::make('category.name')
                                                ->label('Category'),
                                            TextEntry::make('subcategory.name')
                                                ->label('Subcategory'),
                                        ]),
                                ])->columnSpan(5),
                                Group::make([

                                    Section::make('Schedule')
                                        ->columns(2)->columnSpan(4)
                                        ->schema([
                                            TextEntry::make('target_date')
                                                ->placeholder('N/A')
                                                ->label('Target date'),
                                            TextEntry::make('target_time')
                                                ->placeholder('N/A')
                                                ->label('Target time'),
                                        ]),
                                ])->columnSpan(4),

                                Group::make([
                                    Section::make('Priority')
                                        ->columns(2)
                                        ->columnSpan(6)
                                        ->schema([
                                            TextEntry::make('priority')
                                                ->placeholder('N/A')
                                                ->label('Priority'),
                                            TextEntry::make('difficulty')
                                                ->placeholder('N/A')
                                                ->label('Difficulty'),
                                        ]),

                                ])->columnSpan(3),

                                Group::make([

                                    Section::make('Attachments')
                                        ->columns(2)->columnSpan(4)
                                        ->schema(function ($record) {
                                            return [
                                                TextEntry::make('attachment.attachable_id')
                                                    ->formatStateUsing(function ($record) {
                                                        $attachments = json_decode($record->attachment->files, true);

                                                            $html = collect($attachments)->map(function ($path) {
                                                            $fileName = basename($path);
                                                            $fileUrl = Storage::url($path);

                                                            return "<a href='{$fileUrl}' download='{$fileName}'>{$fileName}</a>";
                                                        })->implode('<br>');

                                                        return $html;
                                                    })
                                                    ->openUrlInNewTab()
                                                    ->label(false)
                                                    ->inLineLabel(false)
                                                    ->html(),
                                            ];
                                        }),
                                ])->columnSpan(function ($record) {
                                    $resolved = in_array(RequestStatus::RESOLVED, $record->actions->pluck('status')->toArray());
                                    if ($resolved == true) {
                                        return 4;

                                    } else {
                                        return 6;

                                    }

                                }),
                                Group::make([

                                    Section::make('Remarks')
                                        ->columnSpan(4)
                                        ->schema([
                                            TextEntry::make('remarks')
                                                ->markdown()
                                                ->columnSpan(2)
                                                ->label(false)
                                                ->inLinelabel(false),
                                        ]),
                                ])->columnSpan(function ($record) {
                                    $resolved = in_array(RequestStatus::RESOLVED, $record->actions->pluck('status')->toArray());
                                    if ($resolved == true) {
                                        return 4;

                                    } else {
                                        return 6;

                                    }

                                }),

                                Section::make('Request Rating')
                                    ->columnSpan(4)
                                    ->visible(fn ($record) => in_array(RequestStatus::RESOLVED, $record->actions->pluck('status')->toArray()))
                                    ->schema([
                                        TextEntry::make('remarks')
                                            ->formatStateUsing(function ($record) {
                                                $resolvedActions = $record->action()
                                                    ->where('status', RequestStatus::RESOLVED)
                                                    ->pluck('remarks');

                                                $remarks = $resolvedActions->implode('</br>');

                                                return new HtmlString($remarks ?: 'No survey found   .');
                                            })

                                            ->label(false),
                                    ]),

                                Group::make([
                                    Actions::make([
                                        AcceptAssignmentAction::make(),
                                        RejectAssignmentAction::make(),
                                    ])->hidden(fn ($record) => in_array(RequestStatus::STARTED, $record->actions->pluck('status')->toArray())),
                                ]),

                            ]),
                    ]),

                ActionGroup::make([
                    Action::make('accept')
                        ->visible(fn ($record) => $record->action?->status === RequestStatus::COMPLIED)
                        ->color(RequestStatus::ACCEPTED->getColor())
                        ->icon(RequestStatus::ACCEPTED->getIcon())
                        ->action( function($record){
                            $record->action()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::VERIFIED,
                                'time' => now(),
                            ]);

                            Notification::make()
                                ->title('Accepted Successfully!')
                                ->success()
                                ->send();

                            Notification::make()
                                ->title('Request Accepted')
                                ->body(str("Complied documents have been <b>ACCEPTED</b> by ".auth()->user()->name.'.')->toHtmlString())
                                ->icon(RequestStatus::ACCEPTED->getIcon())
                                ->iconColor(RequestStatus::ACCEPTED->getColor())
                                ->sendToDatabase($record->requestor);
                        }),
                    Action::make('denied')
                        ->visible(fn ($record) => $record->action?->status === RequestStatus::COMPLIED)
                        ->color(RequestStatus::REJECTED->getColor())
                        ->icon(RequestStatus::REJECTED->getIcon())
                        ->action( function($record){
                            $record->action()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::DENIED,
                                'time' => now(),
                            ]);
                            Notification::make()
                                ->title('Completion denied')
                                ->icon(RequestStatus::DENIED->getIcon())
                                ->iconColor(RequestStatus::DENIED->getColor())
                                ->body($record->category->name.' ( '.$record->subcategory->name.' ) '.'</br>'.auth()->user()->name.' has denied the completion of this request')
                                ->sendToDatabase($record->assignees);
                        }),

                    AmmendRecentActionAction::make(),
                    StartedRequestAction::make(),
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
