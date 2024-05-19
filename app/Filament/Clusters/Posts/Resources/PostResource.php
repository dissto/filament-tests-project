<?php

namespace App\Filament\Clusters\Posts\Resources;

use App\Filament\Clusters\Posts;
use App\Filament\Clusters\Posts\Resources\PostResource\Pages\CreatePost;
use App\Filament\Clusters\Posts\Resources\PostResource\Pages\EditPost;
use App\Filament\Clusters\Posts\Resources\PostResource\Pages\ListPosts;
use App\Filament\Clusters\Posts\Resources\PostResource\RelationManagers\CommentsRelationManager;
use App\Models\Post;
use App\Traits\HasTimestampColumns;
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
    use HasTimestampColumns;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Posts::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getNameField(),
                static::getSlugField(),
                static::getContentField(),
                static::getPublishedAtField(),
            ]);
    }

    public static function getNameField(): TextInput
    {
        return TextInput::make('title')
            ->label('Title')
            ->reactive()
            ->afterStateUpdated(fn (Set $set, string $state) => $set('slug', str($state)->slug()))
            ->required();
    }

    public static function getSlugField(): TextInput
    {
        return TextInput::make('slug')
            ->unique(ignoreRecord: true)
            ->required();
    }

    public static function getContentField(): RichEditor
    {
        return RichEditor::make('content')
            ->required();
    }

    public static function getPublishedAtField(): DateTimePicker
    {
        return DateTimePicker::make('published_at')
            ->default(now())
            ->label('Publish at');
    }

    //    public static function getCluster(): ?string
    //    {
    //        return config('project.use_clusters') ? Posts::class : null;
    //    }

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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->withCount('comments');
            })
            ->columns([
                static::getIdColumn(),
                static::getNameColumn(),
                static::getCommentsCountColumn(),
                static::getPublishedAtColumn(),
                static::getCreatedAtColumn(),
                static::getUpdatedAtColumn(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                static::getEditAction(),
                static::getDeleteAction(),
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

    public static function getCommentsCountColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('comments_count')
            ->sortable()
            ->toggleable();
    }

    public static function getPublishedAtColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('published_at')
            ->boolean()
            ->sortable();
    }

    public static function getEditAction(): Tables\Actions\EditAction
    {
        return Tables\Actions\EditAction::make();
    }

    public static function getDeleteAction(): Tables\Actions\DeleteAction
    {
        return Tables\Actions\DeleteAction::make();
    }
}
