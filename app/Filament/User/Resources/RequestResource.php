<?php

namespace App\Filament\User\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\Table\ViewActionsAction;
use App\Filament\User\Resources\RequestResource\Pages;
use App\Models\Attachment;
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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null) | $set('subcategory_id', null))
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name', fn (Builder $query, callable $get) => $query->where('office_id', $get('office_id')))
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->afterStateUpdated(fn (callable $set) => $set('subcategory_id', null))
                    ->required(),
                Forms\Components\Select::make('subcategory_id')
                    ->label('Subcategory')
                    ->relationship('subcategory', 'name', fn (Builder $query, callable $get) => $query->where('category_id', $get('category_id')))
                    ->preload()
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('subject')
                    ->columnSpan(2)
                    ->markAsRequired()
                    ->rule('required')
                    ->maxLength(255),
                Forms\Components\RichEditor::make('remarks')
                    ->columnSpan(2)
                    ->label('Remarks')
                    ->placeholder('Describe the issue')
                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                Forms\Components\DatePicker::make('availability_from')
                    ->seconds(false)
                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                Forms\Components\DatePicker::make('availability_to')
                    ->after('availability_from')
                    ->seconds(false)
                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                Forms\Components\Repeater::make('attachments')
                    ->relationship('attachment')
                    ->label('Attachments')
                    ->columnSpanFull()
                    ->deletable(false)
                    ->addable(false)
                    ->hidden(fn (?Request $record, string $operation) => $operation === 'view' && $record?->attachment()->first()->empty)
                    ->hintIcon('heroicon-o-question-mark-circle')
                    ->hintIconTooltip('Maximum file count of 5 items and file size of 4096 kilobytes.')
                    ->simple(fn (?Request $record) =>
                        Forms\Components\FileUpload::make('paths')
                            ->placeholder(fn (string $operation) => match($operation) {
                                'view' => 'Attached files can be downloaded by clicking the download icon at the left side of the filename',
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
                            ->maxSize(1024*4)
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

                            $fail('Please do not upload the same files (' . $dupes->map->file->join(', ') . ') multiple times.');
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('requestor_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable(),
                Tables\Columns\TextColumn::make('office.acronym'),
                Tables\Columns\TextColumn::make('category.subcategory')
                    ->state(fn (Request $record) => $record->category->name),
                Tables\Columns\TextColumn::make('action.status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('offices')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
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
            ->recordUrl(null)
            ->recordAction(null);
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
