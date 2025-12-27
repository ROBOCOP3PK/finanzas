<?php

namespace App\Http\Requests;

use App\Models\Gasto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlantillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:50',
            'concepto' => 'required|string|max:255',
            'medio_pago_id' => 'required|exists:medios_pago,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => ['required', Rule::in(Gasto::TIPOS)],
            'valor' => 'nullable|numeric|min:0.01',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'concepto.required' => 'El concepto es obligatorio',
            'concepto.max' => 'El concepto no puede exceder 255 caracteres',
            'medio_pago_id.required' => 'Selecciona un medio de pago',
            'medio_pago_id.exists' => 'Medio de pago no válido',
            'categoria_id.exists' => 'Categoría no válida',
            'tipo.required' => 'Selecciona el tipo de gasto',
            'tipo.in' => 'Tipo de gasto no válido',
            'valor.numeric' => 'El valor debe ser un número',
            'valor.min' => 'El valor debe ser mayor a 0',
            'orden.integer' => 'El orden debe ser un número entero',
            'orden.min' => 'El orden no puede ser negativo'
        ];
    }
}
