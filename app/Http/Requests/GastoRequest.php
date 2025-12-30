<?php

namespace App\Http\Requests;

use App\Models\Gasto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'nullable|date',
            'medio_pago_id' => 'nullable|exists:medios_pago,id',
            'categoria_id' => 'required|exists:categorias,id',
            'concepto' => 'nullable|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => ['nullable', Rule::in(Gasto::TIPOS)]
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.date' => 'La fecha no es válida',
            'medio_pago_id.exists' => 'Medio de pago no válido',
            'categoria_id.required' => 'La categoría es obligatoria',
            'categoria_id.exists' => 'Categoría no válida',
            'concepto.string' => 'El concepto debe ser texto',
            'concepto.max' => 'El concepto no puede exceder 255 caracteres',
            'valor.required' => 'El valor es obligatorio',
            'valor.numeric' => 'El valor debe ser un número',
            'valor.min' => 'El valor debe ser mayor a 0',
            'tipo.in' => 'Tipo de gasto no válido'
        ];
    }
}
