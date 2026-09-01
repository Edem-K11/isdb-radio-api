<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('À propos')
                    ->schema([
                        Textarea::make('about_text')
                            ->label('Texte de présentation')
                            ->rows(5)
                            ->maxLength(5000),
                    ]),

                Section::make('Liens')
                    ->columns(2)
                    ->schema([
                        TextInput::make('website_url')->label('Site web')->url()->maxLength(2048),
                        TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(2048),
                        TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(2048),
                        TextInput::make('youtube_url')->label('YouTube')->url()->maxLength(2048),
                        TextInput::make('tiktok_url')->label('TikTok')->url()->maxLength(2048),
                        TextInput::make('android_store_url')
                            ->label('Fiche Play Store')
                            ->url()
                            ->maxLength(2048)
                            ->helperText('Utilisée par le bouton « Évaluer l\'application ».'),
                        TextInput::make('privacy_policy_url')
                            ->label('Politique de confidentialité')
                            ->url()
                            ->maxLength(2048),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_phone')->label('Téléphone')->tel()->maxLength(40),
                        TextInput::make('contact_email')->label('E-mail')->email()->maxLength(160),
                    ]),

                Section::make('Application mobile')
                    ->schema([
                        TextInput::make('min_supported_version')
                            ->label('Version minimale supportée')
                            ->required()
                            ->maxLength(20)
                            ->default('1.0.0')
                            ->helperText('En dessous de cette version, l\'app demande une mise à jour. Format x.y.z'),
                    ]),
            ]);
    }
}
