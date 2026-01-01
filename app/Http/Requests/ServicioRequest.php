<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('valor_estimado') && $this->valor_estimado !== null) {
            $this->merge([
                'valor_estimado' => (int) $this->valor_estimado
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'categoria_id' => 'nullable|exists:categorias,id',
            'icono' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'valor_estimado' => 'nullable|numeric|min:0',
            'referencia' => 'nullable|string|max:255',
            'frecuencia_meses' => 'nullable|integer|min:1|max:12',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'categoria_id.exists' => 'Categoría no válida',
            'valor_estimado.numeric' => 'El valor debe ser un número',
            'valor_estimado.min' => 'El valor no puede ser negativo'
        ];
    }
}
