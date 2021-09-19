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

        // prepare response
        $data = $model->getMedia($request->collection)->toMediaInput($model);

        $initialPreviewConfig = Arr::where($data['initialPreviewConfig'], function ($value, $key) use ($media) {
            return $value['id'] == $media->getKey();
        });

        return response()->json([
            'initialPreviewConfig' => array_values($initialPreviewConfig),
            'initialPreview' => [$media->getUrl()],
            'append' => true
        ]);
    }

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
