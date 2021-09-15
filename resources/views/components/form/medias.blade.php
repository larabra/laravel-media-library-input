 <?php
    $model = Form::getModel();
    
    if($model){
        $mediaData = $model->getMedia($name)->toMediaInput($model);
        $attributes['data-medias'] = e(json_encode($mediaData));
    }

    $input_id = $attributes['id'] ?? 'medias_' . Str::random(10);
    $attributes['id'] = $input_id;
?>


<div class="file-loading d-block">
     {!! Form::file($name, $attributes) !!}
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