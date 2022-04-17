<?php


namespace  App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class AbstractRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $response = response()->json($errors->toArray());

        throw new HttpResponseException($response);
    }


    abstract public function rules(): array;
}
