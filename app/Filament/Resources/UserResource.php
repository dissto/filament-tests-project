<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager;
use App\Models\User;
use App\Traits\HasTimestampColumns;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use HasTimestampColumns;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getNameField(),
                static::getEmailField(),
                static::getPasswordField(),
                static::getVerifiedAtField(),
            ]);
    }

    public static function getNameField(): TextInput
    {
        return TextInput::make('name')
            ->required();
    }

    public static function getEmailField(): TextInput
    {
        return TextInput::make('email')
            ->required()
            ->email();
    }

    public static function getPasswordField(): TextInput
    {
        return TextInput::make('password')
            ->password()
            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->required(fn (string $operation): bool => $operation === 'create')
            ->revealable();
    }

    public static function getVerifiedAtField(): DateTimePicker
    {
        return DateTimePicker::make('email_verified_at');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
            PostsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::getIdColumn(),
                static::getNameColumn(),
                static::getEmailColumn(),
                static::getVerifiedAtColumn(),
                static::getCreatedAtColumn(),
                static::getUpdatedAtColumn(),
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

    public static function getIdColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('id')
            ->sortable()
            ->toggleable();
    }

    public static function getNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable();
    }

    public static function getEmailColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('email')
            ->searchable()
            ->sortable();
    }

    public static function getVerifiedAtColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('email_verified_at')
            ->boolean()
            ->sortable()
            ->tooltip(fn (User $user) => $user->email_verified_at ?? null)
            ->toggleable();
    }
}
