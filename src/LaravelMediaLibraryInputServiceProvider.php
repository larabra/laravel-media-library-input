<?php

namespace Larabra\LaravelMediaLibraryInput;

use Collective\Html\FormFacade as Form;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Larabra\LaravelMediaLibraryInput\Routing\ResourceRegistrarProxy;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class LaravelMediaLibraryInputServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bootRouteResource();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'larabra');
        $this->bootLaravelCollectiveMediaFormInput();


        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'larabra');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    private function bootRouteResource()
    {
        $resourceRegistrar = $this->app->make(ResourceRegistrarProxy::class);
        PendingResourceRegistration::macro('withMedias', function () use ($resourceRegistrar) {
            [$name, $prefix] = $resourceRegistrar->getResourcePrefix($this->name);
            $controller = $this->controller;
            $options = $this->options;

            Route::group(['prefix' => "$prefix"], function ($route) use ($name, $controller, $options, $resourceRegistrar) {
                // create media
                $route->post(
                    $uri = $this->registrar->getResourceUri($name) . '/{' . $name . '}/medias',
                    $action = $resourceRegistrar->getResourceAction($name, $controller, 'createMedia', $options),
                );

                // reorder media
                $route->put(
                    $uri = $this->registrar->getResourceUri($name) . '/{' . $name . '}/medias',
                    $action = $resourceRegistrar->getResourceAction($name, $controller, 'reorderMedia', $options),
                );

                // reorder media
                $route->delete(
                    $uri = $this->registrar->getResourceUri($name) . '/{' . $name . '}/medias/{medias}',
                    $action = $resourceRegistrar->getResourceAction($name, $controller, 'destroyMedia', $options),
                );

                // download media
                $route->get(
                    $uri = $this->registrar->getResourceUri($name) . '/{' . $name . '}/medias/{medias}/download',
                    $action = $resourceRegistrar->getResourceAction($name, $controller, 'downloadMedia', $options),
                );
            });

            return $this;
        });
    }

    private function bootLaravelCollectiveMediaFormInput()
    {
        Form::component('medias', 'larabra::components.form.medias', ['name', 'attributes' => []]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-media-library-input.php', 'laravel-media-library-input');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-media-library-input'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/laravel-media-library-input.php' => config_path('laravel-media-library-input.php'),
        ], 'laravel-media-library-input.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/larabra'),
        ], 'laravel-media-library-input.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/larabra'),
        ], 'laravel-media-library-input.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/larabra'),
        ], 'laravel-media-library-input.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
