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
            'fecha' => 'required|date',
            'medio_pago_id' => 'required|exists:medios_pago,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'concepto' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => ['required', Rule::in(Gasto::TIPOS)]
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha no es válida',
            'medio_pago_id.required' => 'Selecciona un medio de pago',
            'medio_pago_id.exists' => 'Medio de pago no válido',
            'categoria_id.exists' => 'Categoría no válida',
            'concepto.required' => 'El concepto es obligatorio',
            'concepto.max' => 'El concepto no puede exceder 255 caracteres',
            'valor.required' => 'El valor es obligatorio',
            'valor.numeric' => 'El valor debe ser un número',
            'valor.min' => 'El valor debe ser mayor a 0',
            'tipo.required' => 'Selecciona a quién corresponde el gasto',
            'tipo.in' => 'Tipo de gasto no válido'
        ];
    }
}
