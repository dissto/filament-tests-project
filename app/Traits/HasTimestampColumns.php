<?php

namespace App\Traits;

use Filament\Tables\Columns\TextColumn;

trait HasTimestampColumns
{
    public static function getCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable();
    }

    public static function getUpdatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
