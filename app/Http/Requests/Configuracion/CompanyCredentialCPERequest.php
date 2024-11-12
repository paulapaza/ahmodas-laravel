<?php

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCredentialCPERequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /*
         $soap_tipo = $request->soap_tipo;
        $soap_envio = $request->soap_envio;
        $soap_usuario = $request->soap_usuario;
        $soap_clave_usuario = $request->soap_clave_usuario;
        _token: b4bhfdk07AqNyYHAxTkpCG0TqpzO72LtHwcNRkUX
        soap_tipo: interno , Demo o Produccion
        soap_envio: sunat
        soap_usuario: MODDATOS
        soap_clave_usuario: MODDATOS
        */
        return [
            'soap_tipo' => 'required| in:interno,demo,produccion',
            'soap_envio' => 'required| string | in:sunat,ose',
            'soap_usuario' => 'required| string',
            'soap_clave_usuario' => 'required| string',
        ];
    }
}
