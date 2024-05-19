<?php

namespace App\Filament\Clusters\Posts\Resources\PostResource\RelationManagers;

use App\Filament\Resources\CommentResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                CommentResource::getAuthorField(),
                CommentResource::getContentField(),
                CommentResource::getApprovedAtField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                CommentResource::getIdColumn(),
                CommentResource::getAuthorColumn(),
                CommentResource::getContentColumn(),
                CommentResource::getApprovedAtColumn(),
                CommentResource::getCreatedAtColumn(),
                CommentResource::getUpdatedAtColumn(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions(CommentResource::getTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
