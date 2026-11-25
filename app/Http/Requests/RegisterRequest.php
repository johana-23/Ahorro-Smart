<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * La unicidad del correo la decide Spring Boot (HTTP 409), que es el responsable del registro.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 4 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede superar 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
