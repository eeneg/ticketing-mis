<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Admin\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?int $navigationSort = -100;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor Name'),
                Tables\Columns\TextColumn::make('category.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewRequestHistoryAction::make(),
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
