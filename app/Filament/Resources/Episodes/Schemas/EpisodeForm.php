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
                            // Pas de acceptedFileTypes() : les navigateurs
                            // mobiles renvoient souvent un type MIME vide ou
                            // "application/octet-stream" et FilePond bloquait le
                            // fichier avant même l'envoi. On valide plutôt côté
                            // serveur sur le contenu réel du fichier.
                            ->rules(['mimetypes:audio/*,video/mp4,video/3gpp,application/ogg,application/octet-stream'])
                            ->maxSize(204800) // 200 Mo — aligné sur php.ini (upload_max_filesize)
                            ->requiredWithout('audio_url')
                            ->validationMessages([
                                'max' => 'Fichier trop lourd : 200 Mo maximum. Héberge-le ailleurs et colle son lien dans « URL audio externe ».',
                                'mimetypes' => "Ce fichier n'est pas reconnu comme de l'audio. Convertis-le en MP3, ou colle un lien dans « URL audio externe ».",
                                'required_without' => 'Ajoute un fichier audio, ou renseigne une URL audio externe.',
                            ])
                            ->helperText('MP3, AAC, M4A, OGG, OPUS, WAV, FLAC, AMR… — 200 Mo maximum. Fichier plus lourd → héberge-le ailleurs et utilise le champ URL ci-contre.'),
                        TextInput::make('audio_url')
                            ->label('URL audio externe')
                            ->url()
                            ->maxLength(2048)
                            ->requiredWithout('audio_path')
                            ->helperText('Lien direct vers un fichier audio (.mp3, .aac, .m4a, .ogg…).'),
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
