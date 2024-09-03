<?php

namespace App\Filament\Admin\Resources;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile')
                    ->columns(3)
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->avatar()
                            ->directory('avatars'),
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->maxLength(255),
                                Forms\Components\Select::make('office_id')
                                    ->relationship('office', 'name')
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->markAsRequired()
                            ->rules(['required', 'email'])
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options(UserRole::class)
                            ->searchable(),
                        Forms\Components\TextInput::make('number')
                            ->placeholder('9xx xxx xxxx')
                            ->mask('999 999 9999')
                            ->prefix('+63 ')
                            ->rule(fn () => function ($a, $v, $f) {
                                if (! preg_match('/^9.*/', $v)) {
                                    $f('Incorrect number format');
                                }
                            }),
                    ]),
                Forms\Components\Section::make('Password')
                    ->visible(fn ($operation) => $operation === 'create')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state) => ! is_null($state))
                            ->markAsRequired()
                            ->rule('required'),
                        Forms\Components\TextInput::make('confirm_password')
                            ->password()
                            ->markAsRequired()
                            ->rule('required')
                            ->same('password')
                            ->revealable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('number')
                    ->prefix('+63 '),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->hidden(fn ($livewire) => $livewire->activeTab === 'pending')
                    ->onColor('success')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options(UserRole::class)
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->button()
                    ->label('Approved')
                    ->icon(RequestStatus::APPROVED->getIcon())
                    ->color(RequestStatus::APPROVED->getColor())
                    ->visible(fn ($record)=>$record->email_verified_at === null)
                    ->action(function ($record) {
                        $record->update([
                            'id'=>$record->id,
                            'name'=>$record->name,
                            'email'=>$record->email,
                            'password'=>$record->password,
                            'email_verified_at'=>now(),
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->recordUrl(null);
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
