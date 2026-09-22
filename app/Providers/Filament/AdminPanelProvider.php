<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Collection;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Radio ISDB — Administration')
            ->login()
            ->colors([
                'primary' => Color::hex('#1B7A3A'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            // A "back" link above the breadcrumbs — Filament doesn't ship one.
            // Only on a resource's Create/Edit pages, and only pointing up the
            // hierarchy to that resource's own list (never generic browser
            // history): the Dashboard, a list page, and the direct-access
            // singleton settings pages (Diffusion en direct, Réglages — edit
            // pages with no list of their own) have nowhere meaningful to go
            // back to, so none of them get the link at all.
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                function (array $scopes = []): string {
                    $scopes = Collection::make($scopes)->filter(fn ($scope) => is_string($scope) && class_exists($scope));

                    $isEditOrCreatePage = $scopes->contains(
                        fn (string $scope) => is_a($scope, EditRecord::class, allow_string: true)
                            || is_a($scope, CreateRecord::class, allow_string: true),
                    );

                    if (! $isEditOrCreatePage) {
                        return '';
                    }

                    $resourceClass = $scopes->first(
                        fn (string $scope) => is_subclass_of($scope, Resource::class),
                    );

                    // StreamSettingResource/AppSettingResource register their
                    // single Edit page itself as the "index" route (no real
                    // list exists) — hasPage('index') alone can't tell the
                    // two situations apart, so check what page class index
                    // actually resolves to.
                    $indexPage = $resourceClass ? ($resourceClass::getPages()['index'] ?? null)?->getPage() : null;
                    $hasListPage = filled($indexPage) && is_a($indexPage, ListRecords::class, allow_string: true);

                    if (! $hasListPage) {
                        return '';
                    }

                    return view('filament.partials.back-button', [
                        'url' => $resourceClass::getUrl('index'),
                    ])->render();
                },
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
