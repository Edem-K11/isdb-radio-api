<?php

namespace App\Filament\Resources\StreamSettings\Schemas;

use App\Models\StreamSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                        Placeholder::make('logo_preview')
                            ->label('Logo actuel')
                            // See EpisodeForm's cover_preview — same reasoning: the
                            // FileUpload field always writes locally first and gets
                            // promoted to R2 afterward, so its own "existing file"
                            // preview can go stale; read through the model instead.
                            ->content(fn (?StreamSetting $record): HtmlString => new HtmlString(
                                $record?->logoUrl()
                                    ? '<img src="'.e($record->logoUrl()).'" alt="Logo actuel" style="max-width:120px;border-radius:12px;display:block;" />'
                                    : '<span style="color:#6b7280;">Aucun logo pour le moment.</span>'
                            ))
                            ->visible(fn (?StreamSetting $record): bool => $record !== null)
                            ->columnSpanFull(),
                        FileUpload::make('logo_path')
                            ->label('Changer le logo')
                            ->image()
                            ->imageEditor()
                            // Voir EpisodeForm/RemoteUploadPromoter — toujours local
                            // d'abord, promu vers R2 en tâche de fond après coup.
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
