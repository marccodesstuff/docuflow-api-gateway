<?php

namespace App\Filament;

use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\DocumentTypeResource;
use App\Filament\Resources\ProcessingJobResource;
use App\Filament\Resources\ExtractionResultResource;
use App\Filament\Resources\WebhookResource;
use App\Filament\Resources\TenantResource;
use App\Filament\Resources\UserResource;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path(config('filament.admin_path', 'admin'))
            ->login()
            ->registration(false)
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->sidebarCollapsible()
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('DocuFlow')
            ->brandLogo(asset('images/logo.svg'))
            ->favicon(asset('images/favicon.ico'))
            ->colors([
                'primary' => [
                    50 => '#f0f9ff',
                    100 => '#e0f2fe',
                    200 => '#bae6fd',
                    300 => '#7dd3fc',
                    400 => '#38bdf8',
                    500 => '#0ea5e9',
                    600 => '#0284c7',
                    700 => '#0369a1',
                    800 => '#075985',
                    900 => '#0c4a6e',
                    950 => '#082f49',
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                DocumentResource::class,
                DocumentTypeResource::class,
                ProcessingJobResource::class,
                ExtractionResultResource::class,
                WebhookResource::class,
                TenantResource::class,
                UserResource::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                'web',
                'auth',
                \App\Http\Middleware\TenantMiddleware::class,
            ])
            ->authMiddleware([
                'auth',
            ]);
    }
}