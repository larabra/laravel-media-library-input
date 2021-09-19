<?php

namespace Larabra\LaravelMediaLibraryInput\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class MediaCastAttribute implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return $model
            ->getMedia($key)
            ->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'order_column' => $media->order_column,
                    'url' => $media->getUrl(),
                    'responsive' => value(function () use ($media) {
                        $imagesResponsive = $media->responsiveImages('responsive');
                        $response = [
                            'files' => $imagesResponsive
                                ->files
                                ->map(function ($responsiveImage) {
                                    return [
                                        'url' => $responsiveImage->url(),
                                        'width' => $responsiveImage->width(),
                                        'height' => $responsiveImage->height(),
                                    ];
                                })
                        ];
                        if ($placeholder = $imagesResponsive->getPlaceholderSvg()) {
                            $response['placeholder'] = $placeholder;
                        }

                        return $response;
                    }),
                ];
            })
            ->toArray()
            //
        ;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
