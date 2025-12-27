<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbonoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'required|date',
            'valor' => 'required|numeric|min:0.01',
            'nota' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha no es válida',
            'valor.required' => 'El valor es obligatorio',
            'valor.numeric' => 'El valor debe ser un número',
            'valor.min' => 'El valor debe ser mayor a 0',
            'nota.max' => 'La nota no puede exceder 255 caracteres'
        ];
    }
}
