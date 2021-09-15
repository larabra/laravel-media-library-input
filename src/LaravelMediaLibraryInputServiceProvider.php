<?php

namespace Larabra\LaravelMediaLibraryInput;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Collective\Html\FormFacade as Form;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Illuminate\Support\Facades\Route;

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

        $this->bootMediaCollectionMacros();


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
        $this->app->bind(
            \Illuminate\Routing\ResourceRegistrar::class,
            \Larabra\LaravelMediaLibraryInput\Routing\ResourceRegistrar::class
        );
    }

    private function bootLaravelCollectiveMediaFormInput()
    {
        Form::component('medias', 'larabra::components.form.medias', ['name', 'attributes' => []]);
    }

    private function bootMediaCollectionMacros()
    {
        MediaCollection::macro('toMediaInput', function ($model) {
            // $model = Form::getModel();
            $controller = Route::getCurrentRoute()->getController()::class;
            $token = csrf_token();

            $medias = $this->sortBy(function ($media) {
                return $media->order_column;
            });

            $collection = $this->collectionName ?? $this->formFieldName;

            $data = [
                'language' => 'pt-BR',
                'theme' => 'fas',
                'browseOnZoneClick' => true,
                'overwriteInitial' => false, // if true, the medias will be replaced in editor
                'append' => true, // add new medias to the end

                'reorderUrl' => $model ? action([$controller, 'reorderMedia'], $model->getKey()) : null,
                'reorderExtraData' => [
                    '_token' => $token,
                    '_method'=> 'PUT',
                ],

                'showUpload' => $model ? true : false, //
                'uploadUrl' => $model ? action([$controller, 'createMedia'], $model->getKey()) : null, // If this is not set or null, then the upload button action will submit the form.
                'uploadExtraData' => [
                    '_token' => $token,
                    'collection'=> $collection,
                ],

                //
                // https://plugins.krajee.com/file-input/plugin-options#initialPreview
                'initialPreview' => $medias
                    ->map(function ($media) {
                        return $media->getUrl();
                    })
                    ->toArray(),
                //
                // ref https://plugins.krajee.com/file-input/plugin-options#initialPreviewConfig
                'initialPreviewConfig' => $medias
                    ->map(function ($media) use ($model, $controller, $token, $collection) {
                        return [
                            'id' => $media->getKey(),
                            'caption' => $media->name,
                            'type'=> $media->getTypeFromExtension(),
                            'filetype' => $media->mime_type,
                            'size' => $media->size,
                            'previewAsData'=> true,
                            'url' => action([$controller, 'destroyMedia'], [$model->getKey(), $media->getKey()]),
                            'extra' => [
                                '_token' => $token,
                                '_method'=> 'DELETE',
                                'collection'=> $collection,
                            ],
                            'downloadUrl'=> action([$controller, 'downloadMedia'], [$model->getKey(), $media->getKey(), 'collection'=> $collection]),
                        ];
                    })
                    ->toArray(),
            ];

            return $data;
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-media-library-input.php', 'laravel-media-library-input');

        // Register the service the package provides.
        $this->app->singleton('laravel-media-library-input', function ($app) {
            return new LaravelMediaLibraryInput;
        });
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
