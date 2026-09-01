<?php

namespace App\Filament\Resources\StreamSettings\Pages;

use App\Filament\Resources\StreamSettings\StreamSettingResource;
use App\Models\StreamSetting;
use Filament\Resources\Pages\EditRecord;

class EditStreamSetting extends EditRecord
{
    protected static string $resource = StreamSettingResource::class;

    /**
     * This resource edits a single configuration row: always resolve it.
     */
    public function mount(int|string|null $record = null): void
    {
        parent::mount(StreamSetting::current()->getKey());
    }

    public function getBreadcrumb(): string
    {
        return 'Modifier';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
