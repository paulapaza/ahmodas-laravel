<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
/* para personalizar los mensajes de error de validacion
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException; */
use Illuminate\Validation\Rule;




class UserRequest extends FormRequest
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
            
            // incluir ademas numero en el nombre
            'name' => 'required|regex:/^[a-zA-Z0-9ñÑáéíóúü ]+$/|min:3',
            'role' => 'required|alpha|max:255',
            
            'email' =>  [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->id),
            ],
            
        ];
    }

    // renombramos los campos para que se muestren en español, en las vistas
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'role' => 'rol',
            'email' => 'correo electrónico',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * manejador personalizado de errores de validacion.
     * 
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     
    protected function failedValidation(Validator $validator)
    {
        
        $response = [
            "success" => false, // Here I added a new field on JSON response.
            "message" => __("Los datos enviados no son válidos."), // Here I used a custom message.
            "errors" => $validator->errors(), // And do not forget to add the common errors.
        ];

        // Finally throw the HttpResponseException.
        throw new HttpResponseException(response()->json($response, 422));
    }
    */
}