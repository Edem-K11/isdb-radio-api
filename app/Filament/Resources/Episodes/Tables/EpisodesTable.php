<?php

namespace App\Filament\Resources\Episodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EpisodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('cover_path')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->square(),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('duration_seconds')
                    ->label('Durée')
                    ->formatStateUsing(fn (?int $state): string => $state
                        ? sprintf('%d:%02d', intdiv($state, 60), $state % 60)
                        : '—')
                    ->sortable(),
                TextColumn::make('plays_count')
                    ->label('Écoutes')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Publiée')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Publiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_published')
                    ->label('Publiée'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
