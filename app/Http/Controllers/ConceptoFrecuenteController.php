<?php

namespace App\Http\Controllers;

use App\Models\ConceptoFrecuente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConceptoFrecuenteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ConceptoFrecuente::with('medioPago');

        if ($request->boolean('favoritos')) {
            $query->favoritos();
        }

        $limite = $request->input('limite', 10);
        $conceptos = $query->masUsados($limite)->get();

        return response()->json([
            'success' => true,
            'data' => $conceptos
        ]);
    }

    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1'
        ]);

        $conceptos = ConceptoFrecuente::with('medioPago')
            ->buscar($request->q)
            ->masUsados(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conceptos
        ]);
    }

    public function toggleFavorito(Request $request, ConceptoFrecuente $conceptoFrecuente): JsonResponse
    {
        $request->validate([
            'es_favorito' => 'required|boolean'
        ]);

        $conceptoFrecuente->update([
            'es_favorito' => $request->es_favorito
        ]);

        return response()->json([
            'success' => true,
            'data' => $conceptoFrecuente,
            'message' => $request->es_favorito
                ? 'Concepto agregado a favoritos'
                : 'Concepto removido de favoritos'
        ]);
    }

    public function destroy(ConceptoFrecuente $conceptoFrecuente): JsonResponse
    {
        $conceptoFrecuente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Concepto eliminado correctamente'
        ]);
    }
}
