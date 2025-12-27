<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Incluir categorías del usuario y globales (user_id = null)
        $query = Categoria::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->ordenados();

        if ($request->boolean('activas')) {
            $query->activos();
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function store(CategoriaRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $categoria = Categoria::create($data);

        return response()->json([
            'success' => true,
            'data' => $categoria,
            'message' => 'Categoría creada correctamente'
        ], 201);
    }

    public function show(Request $request, Categoria $categoria): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $categoria
        ]);
    }

    public function update(CategoriaRequest $request, Categoria $categoria): JsonResponse
    {
        $user = $request->user();

        if ($categoria->user_id !== null && $categoria->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $categoria->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $categoria,
            'message' => 'Categoría actualizada correctamente'
        ]);
    }

    public function destroy(Request $request, Categoria $categoria): JsonResponse
    {
        $user = $request->user();

        if ($categoria->user_id !== null && $categoria->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        if (!$categoria->puedeEliminarse()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene gastos asociados.'
            ], 422);
        }

        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada correctamente'
        ]);
    }

    public function reordenar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|exists:categorias,id'
        ]);

        foreach ($request->orden as $index => $id) {
            Categoria::where('id', $id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhereNull('user_id');
                })
                ->update(['orden' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}
