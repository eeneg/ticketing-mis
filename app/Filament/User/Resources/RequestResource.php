<?php

namespace App\Filament\User\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\Tables\PublishRequestAction;
use App\Filament\Actions\Tables\RetractRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\User\Resources\RequestResource\Pages;
use App\Models\Category;
use App\Models\Request;
use App\Models\Subcategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                Forms\Components\Section::make()
                    ->visible(fn (string $operation) => $operation !== 'view')
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
                    ->visible(fn (string $operation) => $operation === 'view')
                    ->columnSpan(8)
                    ->columns(2)
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
                        Forms\Components\Hidden::make('requestor_id')
                            ->default(Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('requestor_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(24)
                    ->tooltip(fn (Request $record) => $record->subject),
                Tables\Columns\TextColumn::make('office.acronym')
                    ->limit(12)
                    ->tooltip(fn (Request $record) => $record->office->acronym),
                Tables\Columns\TextColumn::make('category.name')
                    ->limit(36)
                    ->formatStateUsing(fn ($record) => "{$record->category->name} ({$record->subcategory->name})")
                    ->tooltip(fn (Request $record) => "{$record->category->name} ({$record->subcategory->name})"),
                Tables\Columns\TextColumn::make('action.status')
                    ->label('Status')
                    ->badge(),
                Tables\Columns\TextColumn::make('published_at')
                    ->tooltip(fn (Request $record) => $record->published_at?->format('Y-m-d H:i:s'))
                    ->since(),
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
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Request $record) => is_null($status = $record->action?->status) || $status === RequestStatus::RETRACTED),
                Tables\Actions\ViewAction::make()
                    ->modalWidth('6xl'),
                ActionGroup::make([
                    PublishRequestAction::make(),
                    RetractRequestAction::make(),
                    ViewRequestHistoryAction::make(),
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
