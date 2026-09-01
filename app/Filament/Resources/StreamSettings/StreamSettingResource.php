<?php

namespace App\Filament\Resources\StreamSettings;

use App\Filament\Resources\StreamSettings\Pages\EditStreamSetting;
use App\Filament\Resources\StreamSettings\Schemas\StreamSettingForm;
use App\Models\StreamSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class StreamSettingResource extends Resource
{
    protected static ?string $model = StreamSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Diffusion en direct';

    protected static ?string $title = 'Diffusion en direct';

    public static function form(Schema $schema): Schema
    {
        return StreamSettingForm::configure($schema);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => EditStreamSetting::route('/'),
        ];
    }
}
