<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Clusters\Posts\Resources\PostResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                PostResource::getNameField(),
                PostResource::getSlugField(),
                PostResource::getContentField(),
                PostResource::getPublishedAtField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                PostResource::getIdColumn(),
                PostResource::getNameColumn(),
                PostResource::getPublishedAtColumn(),
                PostResource::getCreatedAtColumn(),
                PostResource::getUpdatedAtColumn(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                PostResource::getEditAction(),
                PostResource::getDeleteAction(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
