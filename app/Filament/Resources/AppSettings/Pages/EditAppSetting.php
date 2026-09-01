<?php

namespace App\Filament\Resources\AppSettings\Pages;

use App\Filament\Resources\AppSettings\AppSettingResource;
use App\Models\AppSetting;
use Filament\Resources\Pages\EditRecord;

class EditAppSetting extends EditRecord
{
    protected static string $resource = AppSettingResource::class;

    public function mount(int|string|null $record = null): void
    {
        parent::mount(AppSetting::current()->getKey());
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
