<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor Name'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('remarks')
                    ->html(),
                Tables\Columns\TextColumn::make('priority'),
                Tables\Columns\TextColumn::make('dificulty'),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assignee'),

            ])
            ->filters([
                //
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
