<?php

namespace App\Http\Requests;

use App\Models\Gasto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GastoRecurrenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'concepto' => 'required|string|max:255',
            'medio_pago_id' => 'required|exists:medios_pago,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => ['required', Rule::in(Gasto::TIPOS)],
            'valor' => 'required|numeric|min:0.01',
            'dia_mes' => 'required|integer|min:1|max:31',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'concepto.required' => 'El concepto es obligatorio',
            'concepto.max' => 'El concepto no puede exceder 255 caracteres',
            'medio_pago_id.required' => 'Selecciona un medio de pago',
            'medio_pago_id.exists' => 'Medio de pago no válido',
            'categoria_id.exists' => 'Categoría no válida',
            'tipo.required' => 'Selecciona el tipo de gasto',
            'tipo.in' => 'Tipo de gasto no válido',
            'valor.required' => 'El valor es obligatorio',
            'valor.numeric' => 'El valor debe ser un número',
            'valor.min' => 'El valor debe ser mayor a 0',
            'dia_mes.required' => 'El día del mes es obligatorio',
            'dia_mes.integer' => 'El día debe ser un número entero',
            'dia_mes.min' => 'El día debe ser entre 1 y 31',
            'dia_mes.max' => 'El día debe ser entre 1 y 31'
        ];
    }
}
