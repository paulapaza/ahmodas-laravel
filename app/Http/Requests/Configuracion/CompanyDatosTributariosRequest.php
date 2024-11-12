<?php

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

class CompanyDatosTributariosRequest extends FormRequest
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
        /* $id = 1;
            _token: b4bhfdk07AqNyYHAxTkpCG0TqpzO72LtHwcNRkUX
        razon_social: pedro raul herrera pilco
        tipo_documento: 6
        nro_documento: 10406450258
        direccion_fiscal: calle colon 135
        departamento: AREQUIPA
        provincia: AREQUIPA
        distrito: AREQUIPA
        ubigeo: 040101
        */
        return [
            'tipo_documento' => 'required| string', // 0 -> sin doc 1 -> dni 6 -> ruc
            'nro_documento' => 'required| string | min:11|max:11', 
            'razon_social' => 'required| string',
            'direccion_fiscal' => 'required| string',
            'departamento' => 'required| string',
            'provincia' => 'required| string',
            'distrito' => 'required| string',
            'ubigeo' => 'required| string | min:6|max:6',
        ];
    }
}
