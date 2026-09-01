<?php

namespace App\Filament\Resources\Episodes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EpisodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        // The slug is generated from the title automatically
                        // (see App\Models\Episode::booted); it is not shown here.
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(180)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        FileUpload::make('cover_path')
                            ->label('Image de couverture')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('covers')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->helperText('JPG/PNG/WebP, 8 Mo max.'),
                    ]),

                Section::make('Audio')
                    ->description("Fournis un fichier à téléverser OU une URL externe (au moins l'un des deux). La durée est calculée automatiquement.")
                    ->columns(2)
                    ->schema([
                        FileUpload::make('audio_path')
                            ->label('Fichier audio')
                            ->disk('public')
                            ->directory('episodes')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'audio/mpeg', 'audio/mp3', 'audio/aac',
                                'audio/mp4', 'audio/x-m4a', 'audio/ogg',
                            ])
                            ->maxSize(153600)
                            ->requiredWithout('audio_url')
                            ->helperText('MP3/AAC/M4A/OGG, 150 Mo max.'),
                        TextInput::make('audio_url')
                            ->label('URL audio externe')
                            ->url()
                            ->maxLength(2048)
                            ->requiredWithout('audio_path')
                            ->helperText('Lien direct vers un fichier .mp3/.aac.'),
                    ]),

                Section::make('Publication')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Publiée')
                            ->helperText("Visible dans l'application."),
                        DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->seconds(false)
                            ->helperText('Laisser vide : maintenant, au moment de la publication.'),
                    ]),
            ]);
    }
}
