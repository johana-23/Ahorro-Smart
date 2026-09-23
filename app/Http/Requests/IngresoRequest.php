<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngresoRequest extends FormRequest
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
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required' => 'El monto es obligatorio.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'fecha_ingreso.required' => 'La fecha es obligatoria.',
            'fecha_ingreso.before_or_equal' => 'La fecha no puede ser futura.',
        ];
    }
}
