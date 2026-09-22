<?php

namespace App\Filament\Resources\Episodes\Schemas;

use App\Models\Episode;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                        Placeholder::make('cover_preview')
                            ->label('Jaquette actuelle')
                            // The FileUpload field below always writes to the fast
                            // local disk first, then RemoteUploadPromoter moves the
                            // file to R2 in the background — by the time this page
                            // re-renders (even in the same request, after a redirect),
                            // the file may already be gone from 'public'. Filament's
                            // own "existing file" preview looks at the field's disk
                            // (public) and would show nothing. Reading through the
                            // model's own coverUrl() instead always resolves to
                            // wherever the file actually lives right now.
                            ->content(fn (?Episode $record): HtmlString => new HtmlString(
                                $record?->coverUrl()
                                    ? '<img src="'.e($record->coverUrl()).'" alt="Jaquette actuelle" style="max-width:180px;border-radius:12px;display:block;" />'
                                    : '<span style="color:#6b7280;">Aucune jaquette pour le moment.</span>'
                            ))
                            ->visible(fn (?Episode $record): bool => $record !== null)
                            ->columnSpanFull(),
                        FileUpload::make('cover_path')
                            ->label('Changer la jaquette')
                            ->image()
                            ->imageEditor()
                            // Toujours le disque local rapide, jamais R2 directement —
                            // voir RemoteUploadPromoter : un envoi qui attend sur la
                            // bande passante vers R2 en plein milieu de la requête est
                            // ce qui laissait le formulaire bloqué indéfiniment.
                            ->disk('public')
                            ->directory('covers')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->helperText('JPG/PNG/WebP, 8 Mo max.'),
                    ]),

                Section::make('Audio')
                    ->description('Le fichier téléversé est toujours utilisé en priorité s\'il y en a un. '
                        .'L\'URL externe ne sert que si aucun fichier n\'est téléversé (ex : fichier de plus '
                        .'de 200 Mo hébergé ailleurs). Téléverser un nouveau fichier efface automatiquement '
                        .'l\'URL externe pour éviter toute confusion. La durée est calculée automatiquement.')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('audio_preview')
                            ->label('Audio actuel')
                            // See cover_preview above — same reasoning.
                            ->content(fn (?Episode $record): HtmlString => new HtmlString(
                                $record?->audioUrl()
                                    ? '<audio controls preload="none" style="width:100%;" src="'.e($record->audioUrl()).'"></audio>'
                                    : '<span style="color:#6b7280;">Aucun fichier audio pour le moment.</span>'
                            ))
                            ->visible(fn (?Episode $record): bool => $record !== null)
                            ->columnSpanFull(),
                        FileUpload::make('audio_path')
                            ->label('Changer le fichier audio')
                            // Voir cover_path — toujours local d'abord, promu vers R2
                            // en tâche de fond après coup.
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
                            // Un nouveau fichier téléversé remplace forcément l'ancien
                            // lien externe — sinon celui-ci resterait prioritaire dans
                            // les vieilles données et le nouveau fichier serait ignoré.
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (filled($state)) {
                                    $set('audio_url', null);
                                }
                            })
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
                            ->helperText('Lien direct vers un fichier audio (.mp3, .aac, .m4a, .ogg…). '
                                .'Ignoré tant qu\'un fichier est téléversé ci-contre.'),
                    ]),

                Section::make('Publication')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Publiée')
                            // The date itself is never edited here — Episode::booted()
                            // stamps published_at with now() automatically the first
                            // time this is switched on, and never touches it again.
                            ->helperText('Visible dans l\'application. La date de publication est enregistrée automatiquement.'),
                    ]),
            ]);
    }
}
