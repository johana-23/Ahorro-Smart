<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MetaAhorroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_meta' => ['required', 'string', 'max:100'],
            'monto_objetivo' => ['required', 'numeric', 'min:1'],
            'plazo_meses' => ['required', 'integer', 'min:1', 'max:120'],
            // Manejo de archivos: imagen motivacional PERSONAL (no un catálogo).
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_meta.required' => 'Dale un nombre a tu meta.',
            'monto_objetivo.required' => 'Define el monto que quieres alcanzar.',
            'monto_objetivo.min' => 'El monto objetivo debe ser mayor a 0.',
            'plazo_meses.required' => 'Define en cuántos meses quieres lograrlo.',
            'plazo_meses.integer' => 'El plazo debe ser un número entero de meses.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede pesar más de 2MB.',
        ];
    }
}
