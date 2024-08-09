<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequestResource\Pages;
use App\Models\Category;
use App\Models\Office;
use App\Models\Request;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([

                Forms\Components\Select::make('office_id')
                    ->relationship('office', 'name')
                    ->columnSpan(2)
                    ->options(Office::all()->pluck(value: 'name', key: 'id')->toArray())
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),

                Forms\Components\Select::make('category_id')
                    ->required()
                    ->relationship('category', 'name')
                    ->options(function (callable $get) {
                        $office = Office::find($get('office_id'));
                        if (! $office) {
                            return;
                        } else {
                            return $office->categories->pluck('name', 'id');
                        }
                    })
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('subcategory_id', null)),

                Forms\Components\Select::make('subcategory_id')
                    ->label('Subcategory')
                    ->relationship('subcategory', 'name')
                    ->options(function (callable $get) {
                        $category = Category::find($get('category_id'));
                        if (! $category) {
                            return;
                        } else {
                            return $category->subcategories->pluck('name', 'id');
                        }
                    }),
                Forms\Components\RichEditor::make('remarks')
                    ->columnSpan(2)
                    ->label('Remarks')
                    ->placeholder('Describe the issue'),
                Forms\Components\DateTimePicker::make('availability_from')
                    ->placeholder('24:00')
                    ->displayFormat('Y-m-d')
                    ->seconds(false),

                Forms\Components\DateTimePicker::make('availability_to')
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
            ->columns([
                Tables\Columns\TextColumn::make('remarks'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Office'),
                Tables\Columns\TextColumn::make('subcategory.name')
                    ->label('Subcategory'),
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor'),
                Tables\Columns\TextColumn::make('availability_from')
                    ->label('Available From'),
                Tables\Columns\TextColumn::make('availability_to')
                    ->label('Available To'),
                Tables\Columns\TextColumn::make('priority'),
                Tables\Columns\TextColumn::make('target_date')
                    ->label('Target Date'),
                Tables\Columns\TextColumn::make('target_time')
                    ->label('Target Time'),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequest::route('/create'),
            // 'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }

    public static function getRedirectUrl(): string
    {
        return static::getUrl('create');
    }
}
