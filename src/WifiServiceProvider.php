<?php

namespace Nawasara\Wifi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;

class WifiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara-wifi.php', 'nawasara-wifi');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-wifi');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Guarded — view:cache crash kalau path component tidak ada.
        if (is_dir(__DIR__.'/../resources/views/components')) {
            Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'nawasara-wifi');
        }

        $this->registerLivewire();
        $this->registerApiScopes();
        $this->registerApiRoutes();
    }

    /**
     * Mount route API ke prefix /api/v1/wifi. Cuma jalan kalau nawasara/api
     * terpasang.
     */
    public function registerApiRoutes(): void
    {
        if (! class_exists(\Nawasara\Api\ApiServiceProvider::class)) {
            return;
        }

        $prefix = (string) config('nawasara-api.route.prefix', 'api/v1').'/wifi';

        Route::prefix($prefix)
            ->middleware(['api', 'api.auth', 'api.log'])
            ->name('nawasara-api.wifi.')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register API scope ke nawasara/api scope registry. Guard `class_exists`
     * supaya package tetap jalan kalau nawasara/api tidak ter-install.
     */
    public function registerApiScopes(): void
    {
        if (! class_exists(\Nawasara\Api\Support\ScopeRegistry::class)) {
            return;
        }

        $registry = $this->app->make(\Nawasara\Api\Support\ScopeRegistry::class);

        $registry->register(
            'wifi.point.read',
            'List + detail titik WiFi publik (nama, lokasi, koordinat, status). Untuk plot di peta aplikasi lain.',
        );
    }

    public function registerLivewire(): void
    {
        $namespace = 'Nawasara\\Wifi\\Livewire';
        $basePath = __DIR__.'/Livewire';

        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePath = str_replace('/', '\\', $file->getRelativePathname());
            $class = $namespace.'\\'.Str::beforeLast($relativePath, '.php');

            if (class_exists($class)) {
                $alias = 'nawasara-wifi.'.
                    Str::of($relativePath)
                        ->replace('.php', '')
                        ->replace('\\', '.')
                        ->replace('/', '.')
                        ->explode('.')
                        ->map(fn ($segment) => Str::kebab($segment))
                        ->join('.');

                Livewire::component($alias, $class);
            }
        }
    }
}
