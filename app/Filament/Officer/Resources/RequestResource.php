<?php

namespace App\Filament\Officer\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Officer\Resources\RequestResource\Pages;
use App\Models\Category;
use App\Models\Request;
use App\Models\Subcategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\Section::make('Request')
                    ->columnSpan(8)
                    ->columns(2)
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('office_id')
                            ->relationship('office', 'name')
                            ->columnSpan(2)
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('category_id', null) | $set('subcategory_id', null))
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name', fn (Builder $query, Forms\Get $get) => $query->where('office_id', $get('office_id')))
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('subcategory_id', null))
                            ->required(),
                        Forms\Components\Select::make('subcategory_id')
                            ->relationship('subcategory', 'name', fn (Builder $query, Forms\Get $get) => $query->where('category_id', $get('category_id')))
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('subject')
                            ->columnSpan(2)
                            ->placeholder('Enter the subject of the request')
                            ->markAsRequired()
                            ->rule('required')
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('remarks')
                            ->columnSpan(2)
                            ->label('Remarks')
                            ->placeholder('Provide a detailed description of the issue to ensure that the assigned personnel have a comprehensive understanding of the problem, which will help them address it more effectively.')
                            ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null)
                            ->required(),
                    ]),
                Forms\Components\Group::make()
                    ->columnSpan(4)
                    ->schema([
                        Forms\Components\Section::make('Availability')
                            ->description(fn (string $operation) => $operation !== 'view' ? 'Set your availability date for the request. Leave these fields blank if not necessary.' : null)
                            ->hidden(fn (string $operation, ?Request $record) => $operation === 'view' && $record?->availability_from === null && $record?->availability_to === null)
                            ->compact()
                            ->columns(2)
                            ->collapsed(fn (string $operation) => $operation !== 'view')
                            ->schema([
                                Forms\Components\DatePicker::make('availability_from')
                                    ->label('From')
                                    ->seconds(false)
                                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                                Forms\Components\DatePicker::make('availability_to')
                                    ->label('Until')
                                    ->seconds(false)
                                    ->afterOrEqual('availability_from')
                                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                            ]),
                        Forms\Components\Repeater::make('attachments')
                            ->relationship('attachment')
                            ->label('Attachments')
                            ->columnSpanFull()
                            ->deletable(false)
                            ->addable(false)
                            ->hidden(fn (?Request $record, string $operation) => $operation === 'view' && $record?->attachment()->first()->empty)
                            ->hint(fn (string $operation) => $operation !== 'view' ? 'Help' : null)
                            ->hintIcon(fn (string $operation) => $operation !== 'view' ? 'heroicon-o-question-mark-circle' : null)
                            ->hintIconTooltip('Please upload a maximum file count of 5 items and file size of 4096 kilobytes.')
                            ->helperText(fn (string $operation) => $operation !== 'view' ? 'If necessary, you may upload files that will help the assigned personnel better understand the issue.' : null)
                            ->simple(fn (?Request $record) => Forms\Components\FileUpload::make('paths')
                                ->placeholder(fn (string $operation) => match ($operation) {
                                    'view' => 'Click the icon at the left side of the filename to download',
                                    default => null,
                                })
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, string $operation) use ($record) {
                                    return $operation === 'create'
                                        ? str(str()->ulid())
                                            ->lower()
                                            ->append('.'.$file->getClientOriginalExtension())
                                        : str(str()->ulid())
                                            ->prepend("request-{$record->id}-")
                                            ->lower()
                                            ->append(".{$file->getClientOriginalExtension()}");
                                })
                                ->directory('attachments')
                                ->storeFileNamesIn('files')
                                ->multiple()
                                ->maxFiles(5)
                                ->downloadable()
                                ->previewable(false)
                                ->maxSize(1024 * 4)
                                ->removeUploadedFileButtonPosition('right')
                            )
                            ->rule(fn () => function ($attribute, $value, $fail) {
                                $files = collect(current($value)['paths'])->map(function (TemporaryUploadedFile|string $file) use ($value) {
                                    return [
                                        'file' => $file instanceof TemporaryUploadedFile
                                            ? $file->getClientOriginalName()
                                            : current($value)['files'][$file],
                                        'hash' => $file instanceof TemporaryUploadedFile
                                            ? hash_file('sha512', $file->getRealPath())
                                            : hash_file('sha512', storage_path("app/public/$file")),
                                    ];
                                });

                                if (($duplicates = $files->duplicates('hash'))->isNotEmpty()) {
                                    $dupes = $files->filter(fn ($file) => $duplicates->contains($file['hash']))->unique();

                                    $fail('Please do not upload the same files ('.$dupes->map->file->join(', ').') multiple times.');
                                }
                            }),
                        Forms\Components\Fieldset::make('Tags')
                            ->columns(1)
                            ->hidden(fn (string $operation, ?Request $record) => $operation === 'view' && $record?->tags->isEmpty())
                            ->schema([
                                Forms\Components\CheckboxList::make('tags')
                                    ->hint(fn (string $operation) => $operation !== 'view' ? 'Select tags that best describe the request issue. This will help in categorizing the request.' : '')
                                    ->hiddenLabel()
                                    ->reactive()
                                    ->columns(2)
                                    ->relationship(titleAttribute: 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                        $query->orWhere(function (Builder $query) use ($get) {
                                            $query->where('taggable_type', Category::class);

                                            $query->where('taggable_id', $get('category_id'));
                                        });

                                        $query->orWhere(function (Builder $query) use ($get) {
                                            $query->where('taggable_type', Subcategory::class);

                                            $query->where('taggable_id', $get('subcategory_id'));
                                        });
                                    })
                                    ->searchable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('action', function (Builder $query) {
                    $query->whereNot('status', RequestStatus::RETRACTED);
                });

                $query->where('office_id', Auth::user()->office_id);

                $query->orderBy();
            })
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable(),
                Tables\Columns\TextColumn::make('office.name'),
                Tables\Columns\TextColumn::make('action.status')
                    ->label('Status')
                    ->badge(),
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
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('6xl'),
                ActionGroup::make([
                    Action::make('Reassign')
                        ->color('success')
                        ->icon('heroicon-s-pencil-square')
                        ->form([
                            Forms\Components\Select::make('priority')
                                ->label('New Priority')
                                ->options([
                                    '1' => '1',
                                    '2' => '2',
                                    '3' => '3',
                                    '4' => '4',
                                    '5' => '5',
                                ])
                                ->required(),
                            RichEditor::make('remarks')
                                ->label('New Remarks')
                                ->required(),
                            Forms\Components\Select::make('user_ids')
                                ->label('Assignees')
                                ->default('Hello')
                                ->options(User::query()->where('role', 'support')->pluck('name', 'id'))
                                ->multiple(),
                        ])
                        ->action(function ($data, $record) {
                            $userIds = $data['user_ids'] ?? [];

                            $upsert_records = collect($userIds)->map(function ($id) use ($record) {
                                return [
                                    'assigner_id' => Auth::id(),
                                    'request_id' => $record->id,
                                    'user_id' => $id,
                                    'response' => 'pending',
                                ];
                            })->toArray();

                            $record->assignees()->upsert(
                                $upsert_records,
                                ['request_id', 'user_id'],
                                ['user_id'],
                            );

                            $record->action()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::ASSIGNED,
                                'remarks' => $record['remarks'],
                                'time' => now(),
                            ]);

                            Notification::make()
                                ->title('Request Reassigned Successfully')
                                ->success()
                                ->send();

                            $record->update(['priority' => $data['priority']]);
                        })
                        ->visible(function ($record) {
                            $latestAction = $record->actions()->latest()->first();
                            $latestActionStatus = $latestAction?->status;

                            return $latestActionStatus == RequestStatus::ACCEPTED;
                        }),
                    Action::make('Approve')
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
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::APPROVED,
                                'remarks' => $record['remarks'],
                                'time' => now(),
                            ]);

                            Notification::make()
                                ->title('Request Assigned Successfully')
                                ->success()
                                ->send();
                            $record->update(['priority' => $data['priority']]);

                        })
                        ->visible(function ($record) {
                            $latestAction = $record->actions()->latest()->first();
                            $latestActionStatus = $latestAction?->status;

                            return $latestActionStatus == RequestStatus::PUBLISHED;
                        }),
                    ViewRequestHistoryAction::make(),
                    Action::make('Reject')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->action(function ($record) {
                            $record->action()->create([
                                'request_id' => $record->id,
                                'user_id' => Auth::id(),
                                'status' => RequestStatus::REJECTED,
                                'remarks' => $record['remarks'],
                                'time' => now(),
                            ]);

                        })
                        ->visible(function ($record) {
                            $latestAction = $record->actions()->latest()->first();
                            $latestActionStatus = $latestAction?->status;

                            return $latestActionStatus == '';
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
