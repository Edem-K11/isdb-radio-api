<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        // The slug is generated from the name automatically
        // (see App\Models\Category::booted).
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(120),
                ColorPicker::make('color')
                    ->label('Couleur')
                    ->required()
                    ->default('#1B7A3A'),
                TextInput::make('sort_order')
                    ->label("Ordre d'affichage")
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
