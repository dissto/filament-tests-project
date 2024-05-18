<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getNameFormField(),
                static::getSlugFormField(),
                static::getContentFormField(),
                static::getPublishedAtFormField(),
            ]);
    }

    public static function getNameFormField(): TextInput
    {
        return TextInput::make('title')
            ->label('Title')
            ->reactive()
            ->afterStateUpdated(fn (Set $set, string $state) => $set('slug', str($state)->slug()))
            ->required();
    }

    public static function getSlugFormField(): TextInput
    {
        return TextInput::make('slug')
            ->unique(ignoreRecord: true)
            ->required();
    }

    public static function getContentFormField(): RichEditor
    {
        return RichEditor::make('content')
            ->required();
    }

    public static function getPublishedAtFormField(): DateTimePicker
    {
        return DateTimePicker::make('published_at')
            ->default(now())
            ->label('Publish at');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::getIdColumn(),
                static::getNameColumn(),
                static::getPublishedAtColumn(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getIdColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('id')
            ->toggleable()
            ->sortable();
    }

    public static function getNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('title')
            ->description(fn (Post $post) => $post->slug)
            ->searchable()
            ->sortable();
    }

    public static function getPublishedAtColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('published_at')
            ->boolean()
            ->sortable();
    }
}
