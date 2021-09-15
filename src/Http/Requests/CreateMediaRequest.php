<?php

namespace Larabra\LaravelMediaLibraryInput\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMediaRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @link https://laravel.com/docs/master/validation#available-validation-rules
     * @return array
     */
    public function rules()
    {
        return [
            "collection" => [
                'required',
                "string",
                "max:50",
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function withValidator($validator)
    {
        if($input = $this->collection){
            $validator->sometimes($input, 'required|file', function ($input) {
                return true;
            });
        }
    }
}
