<?php

namespace Larabra\LaravelMediaLibraryInput\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelMediaLibraryInput extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-media-library-input';
    }
}
