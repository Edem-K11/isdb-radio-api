<?php

namespace App\Filament\Resources\StreamSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StreamSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Flux audio')
                    ->description("L'URL lue par l'application pour le direct.")
                    ->columns(2)
                    ->schema([
                        TextInput::make('stream_url')
                            ->label('URL du flux')
                            ->url()
                            ->required()
                            ->maxLength(2048)
                            ->columnSpanFull()
                            ->placeholder('https://exemple.com/stream.mp3'),
                        TextInput::make('backup_url')
                            ->label('URL de secours')
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        Select::make('codec')
                            ->label('Codec')
                            ->options(['mp3' => 'MP3', 'aac' => 'AAC'])
                            ->default('mp3')
                            ->required(),
                        Toggle::make('is_on_air')
                            ->label("A l'antenne")
                            ->helperText("Desactive pour afficher le message hors antenne dans l'app.")
                            ->default(true),
                    ]),

                Section::make('Identité de la station')
                    ->columns(2)
                    ->schema([
                        TextInput::make('station_name')
                            ->label('Nom de la station')
                            ->required()
                            ->maxLength(120)
                            ->default('Radio ISDB'),
                        TextInput::make('slogan')
                            ->label('Slogan')
                            ->maxLength(160),
                        TextInput::make('offline_message')
                            ->label('Message hors antenne')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->helperText('JPG/PNG/WebP, 8 Mo max.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
