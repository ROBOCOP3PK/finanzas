<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlantillaRequest;
use App\Models\Plantilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlantillaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Plantilla::with(['medioPago', 'categoria'])->ordenadas();

        if ($request->boolean('activas')) {
            $query->activas();
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function rapidas(): JsonResponse
    {
        $plantillas = Plantilla::with(['medioPago', 'categoria'])
            ->activas()
            ->masUsadas(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plantillas
        ]);
    }

    public function store(PlantillaRequest $request): JsonResponse
    {
        $plantilla = Plantilla::create($request->validated());
        $plantilla->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $plantilla,
            'message' => 'Plantilla creada correctamente'
        ], 201);
    }

    public function show(Plantilla $plantilla): JsonResponse
    {
        $plantilla->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $plantilla
        ]);
    }

    public function update(PlantillaRequest $request, Plantilla $plantilla): JsonResponse
    {
        $plantilla->update($request->validated());
        $plantilla->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $plantilla,
            'message' => 'Plantilla actualizada correctamente'
        ]);
    }

    public function destroy(Plantilla $plantilla): JsonResponse
    {
        $plantilla->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plantilla eliminada correctamente'
        ]);
    }

    public function usar(Request $request, Plantilla $plantilla): JsonResponse
    {
        $request->validate([
            'fecha' => 'required|date',
            'valor' => 'nullable|numeric|min:0.01'
        ]);

        $gasto = $plantilla->usar(
            $request->fecha,
            $request->valor
        );

        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto,
            'message' => 'Gasto registrado desde plantilla'
        ], 201);
    }

    public function reordenar(Request $request): JsonResponse
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|exists:plantillas,id'
        ]);

        foreach ($request->orden as $index => $id) {
            Plantilla::where('id', $id)->update(['orden' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}
