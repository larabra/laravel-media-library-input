<?php

namespace Larabra\LaravelMediaLibraryInput\Models;

use Illuminate\Support\Arr;

trait MediableModel
{
    public static function bootMediableModel()
    {
        static::saving(function ($model) {
            if (
                $model->isDirty() === false &&
                request()->allFiles()
            ) {
                $model->updated_at = now();
            }
        });

        $mediaCallback = function ($model) {
            $files = request()->allFiles();

            if (empty($files)) {
                return;
            }

            foreach ($files as $collection => $medias) {
                foreach (Arr::wrap($medias) as $media) {
                    $model
                        ->addMedia($media) //starting method
                        ->preservingOriginal() //middle method
                        ->toMediaCollection($collection) //finishing method
                    ;
                }
            }
        };
        static::created($mediaCallback);
        static::updated($mediaCallback);
    }
}
