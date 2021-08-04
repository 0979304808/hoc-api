<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostCreate extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string:between:3,255',
            'image' => 'required|mimes:jpeg,png,jpg|between:3,255',
            'description' => 'required|between:3,255' ,
        ];
    }
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'string' => ':attribute phải là chuỗi',
            'between' => ':attribute phải từ :min ký tự đến :max ký tự',
            'mimes' => ':attribute phải thuộc tệp jpeg,png,jpg '
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['error' => true, 'message' => $validator->errors()->first()],403 ));
    }
}
