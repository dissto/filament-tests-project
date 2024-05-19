<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\Posts;
use App\Filament\Clusters\Posts\Resources\PostResource;
use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use App\Traits\HasTimestampColumns;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommentResource extends Resource
{
    use HasTimestampColumns;

    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Posts::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                static::getAuthorField(),
                static::getContentField(),
                static::getApprovedAtField(),
            ]);
    }

    public static function getAuthorField(): Select
    {
        return Select::make('user_id')
            ->relationship('author', 'name')
            ->required();
    }

    public static function getContentField(): RichEditor
    {
        return RichEditor::make('content')
            ->required();
    }

    public static function getApprovedAtField(): DatePicker
    {
        return DatePicker::make('approved_at');
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
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getTableActions(): array
    {
        return [
            static::getGoToPostAction(),
            static::getEditAction(),
            static::getDeleteAction(),
        ];
    }

    public static function getGoToPostAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('go_to_post')
            ->url(function (Comment $comment) {
                if (! $comment->post) {
                    return null;
                }

                return PostResource::getUrl('edit', ['record' => $comment->post]);
            });
    }

    public static function getEditAction(): Tables\Actions\EditAction
    {
        return Tables\Actions\EditAction::make();
    }

    public static function getDeleteAction(): Tables\Actions\DeleteAction
    {
        return Tables\Actions\DeleteAction::make();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::getIdColumn(),
                static::getPostColumn(),
                static::getAuthorColumn(),
                static::getContentColumn(),
                static::getApprovedAtColumn(),
                static::getCreatedAtColumn(),
                static::getUpdatedAtColumn(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                static::getGoToPostAction(),
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
            ->sortable();
    }

    public static function getPostColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('post.id')
            ->sortable(['post_id'])
            ->searchable()
            ->toggleable(isToggledHiddenByDefault: true)
            ->url(function (Comment $comment) {

                if (! $comment->post) {
                    return null;
                }

                return PostResource::getUrl('edit', ['record' => $comment->post]);
            }, shouldOpenInNewTab: true);
    }

    public static function getAuthorColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('author.name')
            ->sortable()
            ->searchable()
            ->url(function (Comment $comment) {

                if (! $comment->author) {
                    return null;
                }

                return UserResource::getUrl('edit', ['record' => $comment->author]);
            }, shouldOpenInNewTab: true);
    }

    public static function getContentColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('content')
            ->sortable()
            ->searchable()
            ->description(function (Comment $comment) {
                return $comment->post?->title;
            }, 'above')
            ->limit(100)
            ->wrap();
    }

    public static function getApprovedAtColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('approved_at')
            ->boolean()
            ->tooltip(fn (Comment $comment) => $comment?->approved_at)
            ->sortable();
    }
}
