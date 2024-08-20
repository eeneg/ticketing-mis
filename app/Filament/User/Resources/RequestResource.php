<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('office_id')
                    ->relationship('office', 'name')
                    ->columnSpan(2)
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),
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
                Forms\Components\Hidden::make('requestor_id')
                    ->default(Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('requestor_id', Auth::id()))
            ->columns([
                Tables\Columns\TextColumn::make('office.name')->label('Office'),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('subcategory.name')->label('Subcategory'),
                Tables\Columns\TextColumn::make('requestor.name')->label('Requestor'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->color('success'),
                ActionGroup::make([
                    Action::make('CloseTicket')
                        ->url('')
                        ->openUrlInNewTab()
                        ->color('danger'),
                    ViewAction::make('viewactions')
                        ->color('primary')
                        ->icon('heroicon-s-folder')
                        ->slideOver()
                        ->action(fn (Request $record) => $record->viewactions())
                        ->modalContent(function (Request $record) {
                            $relatedRecords = $record->actions()->get();

                            return view('filament.officer.resources.request-resource.pages.actions.viewactions', [
                                'records' => $relatedRecords,
                            ]);
                        }),
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
