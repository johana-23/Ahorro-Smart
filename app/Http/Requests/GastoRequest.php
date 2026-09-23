<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion' => ['nullable', 'string', 'max:200'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha_gasto' => ['required', 'date', 'before_or_equal:today'],
            'tipo_gasto' => ['required', 'in:fijo,variable'],
            'categoria_idcategoria' => ['nullable', 'exists:categoria,idcategoria'],
            'categoria_nueva' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required' => 'El monto es obligatorio.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'fecha_gasto.required' => 'La fecha es obligatoria.',
            'tipo_gasto.required' => 'Selecciona si es un gasto fijo o variable.',
            'tipo_gasto.in' => 'El tipo de gasto no es válido.',
            'categoria_idcategoria.exists' => 'La categoría seleccionada no es válida.',
        ];
    }
}
