<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ingresos_aproximados' => ['required', 'numeric', 'min:0'],
            'gastos_mensuales' => ['required', 'numeric', 'min:0'],
            'dinero_bolsillo' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'ingresos_aproximados.required' => 'Ingresa tus ingresos aproximados.',
            'ingresos_aproximados.numeric' => 'Los ingresos deben ser un número.',
            'gastos_mensuales.required' => 'Ingresa tus gastos mensuales aproximados.',
            'gastos_mensuales.numeric' => 'Los gastos deben ser un número.',
            'dinero_bolsillo.required' => 'Ingresa tu dinero de bolsillo disponible.',
            'dinero_bolsillo.numeric' => 'El dinero de bolsillo debe ser un número.',
        ];
    }
}
