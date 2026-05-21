<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->components->addSecurityScheme(
                    'sanctumBearer',
                    SecurityScheme::http('bearer')
                        ->as('sanctumBearer')
                        ->setDescription('Используйте токен Laravel Sanctum, полученный через POST /api/admin/login, в заголовке Authorization: Bearer <token>.')
                );
            })
            ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo): void {
                if (! in_array('auth:sanctum', $routeInfo->route->gatherMiddleware(), true)) {
                    return;
                }

                $operation->addSecurity(new SecurityRequirement([
                    'sanctumBearer' => [],
                ]));
            });
    }
}
