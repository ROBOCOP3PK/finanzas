<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'icono' => 'nullable|string|max:50',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'icono.max' => 'El icono no puede exceder 50 caracteres',
            'color.required' => 'El color es obligatorio',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF5733)',
            'orden.integer' => 'El orden debe ser un número entero',
            'orden.min' => 'El orden no puede ser negativo'
        ];
    }
}
