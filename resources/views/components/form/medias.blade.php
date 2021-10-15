<?php
    $model = Form::getModel();
    $controller = Route::getCurrentRoute()->getController()::class;
    $token = csrf_token();
    $collectionName = $fieldName = $name;

    $mediaData = [
        'language' => 'pt-BR',
        'theme' => 'fas',
        'browseOnZoneClick' => true,
        'overwriteInitial' => false, // if true, the medias will be replaced in editor
        'append' => true, // add new medias to the end
        //'showRemove'=> true,

        'reorderUrl' => $model ? action([$controller, 'reorderMedia'], $model->getKey()) : null,
        'reorderExtraData' => [
            '_token' => $token,
            '_method' => 'PUT',
        ],

        'showUpload' => $model ? true : false, //
        'uploadUrl' => $model ? action([$controller, 'createMedia'], $model->getKey()) : null, // If this is not set or null, then the upload button action will submit the form.
        'uploadExtraData' => [
            '_token' => $token,
            'collection' => $collectionName,
        ],
        
        // 'initialPreview' => [],
        // 'initialPreviewConfig' => [],
    ];
    
    if($model){
        $medias = $model->getMedia($collectionName)->sortBy(function ($media) {
            return $media->order_column;
        });
        $mediaData['initialPreview'] = $medias
            ->map(function ($media) {
                return $media->getUrl();
            })
            ->toArray()
        ;
        $mediaData['initialPreviewConfig'] = $medias
            ->map(function ($media) use ($model, $controller, $token, $collectionName) {
                return [
                    'id' => $media->getKey(),
                    'caption' => $media->name,
                    'type' => $media->getTypeFromExtension(),
                    'filetype' => $media->mime_type,
                    'size' => $media->size,
                    'previewAsData' => true,
                    'url' => action([$controller, 'destroyMedia'], [$model->getKey(), $media->getKey()]),
                    'extra' => [
                        '_token' => $token,
                        '_method' => 'DELETE',
                        'collection' => $collectionName,
                    ],
                    'downloadUrl' => action(
                        [$controller, 'downloadMedia'], 
                        [$model->getKey(), $media->getKey(), 'collection' => $collectionName]
                    ),
                ];
            })
            ->toArray()
        ;
    }
    $userMediaConfig = data_get($attributes, 'data-medias', []);
    $mediaData = array_merge($mediaData, $userMediaConfig);
    $attributes['data-medias'] = e(json_encode($mediaData));

    $input_id = $attributes['id'] ?? 'medias_' . Str::random(10);
    $attributes['id'] = $input_id;

    $inputName = 
        array_key_exists('multiple', $attributes) && $attributes['multiple']
        ? Str::finish($name, '[]')
        : $name
    ;
?>


<div class="file-loading d-block">
     {!! Form::file($inputName, $attributes) !!}
</div>
@push('page_css')

@endpush

@push('page_scripts')
 <script>
    $(function() {
        $(document).ready(function() {
            let input = document.querySelector('#{{ $input_id }}');
            let medias = JSON.parse(input.dataset.medias);
            $(input)
                .fileinput(medias)
                .on('filesorted', function(event, params) {
                    let order = params.stack.map(media => media.id);

                    $.ajax({
                        method: 'POST',
                        url: medias.reorderUrl,
                        data: $.extend(true, medias.reorderExtraData, {order})
                    })
                });
        });
    })
</script>
@endpush