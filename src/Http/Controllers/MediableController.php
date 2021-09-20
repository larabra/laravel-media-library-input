<?php

namespace Larabra\LaravelMediaLibraryInput\Http\Controllers;

use App\Repositories\BaseRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laracasts\Flash\Flash;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait MediableController
{
    private function model($id){
        // get respository
        $repository = collect(get_object_vars($this))
            ->filter(function ($v, $k) {
                return $v instanceof BaseRepository;
            })
            ->keys()
            ->first();

        //retrive model
        $model = $this->{$repository}->find($id);

        // get model name
        $modelName = app()->make($this->{$repository}->model())->getTable();

        // check if model exists
        if (empty($model)) {
            Flash::error(trans('messages.not_found', ['model' => trans("models/$modelName.singular")]));

            return redirect()->route("$modelName.index");
        }

        return [$model, $modelName];
    }

    /**
     * Add one or more media to model
     *
     * @param Request $request
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     * @return Response
     */
    public function createMedia(Request $request, $id)
    {
        $model = $this->model($id);
        if($model instanceof RedirectResponse){
            return $model;
        }
        [$model, $modelName] = $model;

        // validate the request
        $rules = [
            'collection' => 'required|string|max:50',
        ];
        if ($collection = $request->collection) {
            $rules = array_merge(
                $rules,
                [
                    "$collection" => 'nullable|file',
                ]
            );
        }
        $this->validate($request, $rules, $messages = [], $attrs = trans("models/$modelName.fields"));

        // check if request has a file to store
        if ($request->file($request->collection) === null) {
            return response()->noContent();
        }

        // store media
        $media = $model
            ->addMediaFromRequest($request->collection)
            ->preservingOriginal()
            ->toMediaCollection($request->collection)
            //
        ;

        $controller = get_class($this);
        return response()->json([
            'initialPreview' => [$media->getUrl()],
            'initialPreviewConfig' => [
                [
                    'id' => $media->getKey(),
                    'caption' => $media->name,
                    'type' => $media->getTypeFromExtension(),
                    'filetype' => $media->mime_type,
                    'size' => $media->size,
                    'previewAsData' => true,
                    'url' => action([$controller, 'destroyMedia'], [$model->getKey(), $media->getKey()]),
                    'extra' => [
                        '_token' => csrf_token(),
                        '_method' => 'DELETE',
                        'collection' => $request->collection,
                    ],
                    'downloadUrl' => action(
                        [$controller, 'downloadMedia'], 
                        [$model->getKey(), $media->getKey(), 'collection' => $request->collection]
                    ),
                ]
            ],
            'append' => true
        ]);
    }

    /**
     * Delete a model media in collection
     *
     * @param Request $request
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     * @param string|int|\Illuminate\Database\Eloquent\Model $media_id
     * @return Response
     */
    public function destroyMedia(Request $request, $id, $media_id)
    {
        $model = $this->model($id);
        if($model instanceof RedirectResponse){
            return $model;
        }
        [$model, $modelName] = $model;

        $media = $model->getMedia($request->collection)->where('id', $media_id)->first();

        $model->deleteMedia($media);

        return response()->noContent();
    }

    /**
     * Reorder all medias in collection
     *
     * @param Request $request
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     * @return Response
     */
    public function reorderMedia(Request $request, $id)
    {
        $model = $this->model($id);
        if($model instanceof RedirectResponse){
            return $model;
        }
        [$model, $modelName] = $model;

        Media::setNewOrder($request->order);

        return response()->noContent();
    }

    /**
     * Force download of a media
     *
     * @param Request $request
     * @param string|int|\Illuminate\Database\Eloquent\Model $id
     * @param string|int|\Illuminate\Database\Eloquent\Model $media_id
     * @return Response
     */
    public function downloadMedia(Request $request, $id, $media_id)
    {
        $model = $this->model($id);
        if($model instanceof RedirectResponse){
            return $model;
        }
        [$model, $modelName] = $model;

        $media = $model->getMedia($request->collection)->where('id', $media_id)->first();

        return response()->download($media->getPath(), $media->file_name);
    }
}
