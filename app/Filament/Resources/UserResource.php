<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->avatar()
                    ->directory('avatars'),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('role')
                    ->options(UserRole::class)
                    ->native(false),
                Forms\Components\Select::make('office_id')
                    ->relationship('office', 'name')
                    ->native(false),
                Forms\Components\TextInput::make('number')
                    ->prefix('+63')
                    ->rule(fn () => function ($a, $v, $f) {
                        if (
                            ! preg_match('/^9\d{9}$/', $v)
                        ) {
                            $f('Incorrect number format');
                        }

                    })
                    ->rule('numeric'),
                Forms\Components\TextInput::make('email')
                    ->rule(fn () => function ($a, $v, $f) {
                        if (! preg_match('/^.*@.*$/', $v)) {
                            $f('Invalid Email Format!');
                        }
                    }),
                Forms\Components\TextInput::make('password')
                    ->required(fn ($operation) => $operation === 'create')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state) => ! is_null($state)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->label('Avatar'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role'),
                Tables\Columns\TextColumn::make('number')
                    ->label('Phone Number'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
