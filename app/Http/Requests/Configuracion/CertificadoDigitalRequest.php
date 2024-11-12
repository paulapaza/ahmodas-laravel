<?php

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

class CertificadoDigitalRequest extends FormRequest
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
        return [
            'certificado_file' => ['required', 'file', 'max:2048'],
            'certificado_pass' => ['required', 'string', 'max:255'],
            'certificado_caducidad' => ['required', 'date'],
        ];
    }
}
