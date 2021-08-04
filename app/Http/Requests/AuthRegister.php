<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthRegister extends FormRequest
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

    /**Ư
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|between:3,20',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required|between:3,20|confirmed'
        ];
    }
    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'email' => ':attribute không hợp lệ ví dụ admin@gmail.com',
            'between' =>':attribute phải từ  :min ký tự đến :max ký tự',
            'confirmed' => 'Nhập lại :attribute không khớp',
            'unique' => ':attribute đã tồn tại'

        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['error' => true, 'message' => $validator->errors()->first()],403 ));
    }
}
