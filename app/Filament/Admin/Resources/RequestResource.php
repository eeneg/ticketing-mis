<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

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
            ->actions([
                ActionGroup::make([
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
        ];
    }
}
