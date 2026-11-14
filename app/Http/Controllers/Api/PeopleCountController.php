<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PeopleCount;
use Illuminate\Support\Facades\Validator;

class PeopleCountController extends Controller
{
    /**
     * POST: Registrar cantidad de personas detectadas.
     * Ejemplo payload: { "cantidad": 25 }
     * Ejemplo respuesta: { "mensaje": "Registro creado correctamente", "cantidad": 25 }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:0',
            'fecha_hora' => 'nullable|date',
        ], [
            'cantidad.required' => 'El campo cantidad es obligatorio.',
            'cantidad.integer' => 'El campo cantidad debe ser un número entero.',
            'cantidad.min' => 'El campo cantidad no puede ser negativo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        $registro = PeopleCount::create([
            'cantidad' => $validated['cantidad'],
            'fecha_hora' => $validated['fecha_hora'] ?? now(),
        ]);

        return response()->json([
            'mensaje' => 'Registro creado correctamente',
            'cantidad' => (int) $registro->cantidad,
            'id' => $registro->id,
            'fecha_hora' => $registro->fecha_hora->toDateTimeString(),
            'success' => true,
        ], 201);
    }

    /**
     * GET: Consultar cantidad de personas.
     * Devuelve la suma de las mediciones y el historial de registros individuales.
     */
    public function index(Request $request)
    {
        $limit = (int) $request->input('limit', 100);
        if ($limit <= 0 || $limit > 1000) {
            $limit = 100;
        }

        $suma = (int) PeopleCount::sum('cantidad');
        $totalMediciones = (int) PeopleCount::count();

        $ultimoRegistro = PeopleCount::orderBy('fecha_hora', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $registros = PeopleCount::orderBy('fecha_hora', 'asc')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'cantidad' => (int) $item->cantidad,
                    'fecha_hora' => $item->fecha_hora ? $item->fecha_hora->toDateTimeString() : null,
                    'hora' => $item->fecha_hora ? $item->fecha_hora->format('H:i:s') : null,
                    'fecha' => $item->fecha_hora ? $item->fecha_hora->format('Y-m-d') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'suma' => $suma,
            'total' => $suma,
            'total_mediciones' => $totalMediciones,
            'ultimo_registro' => $ultimoRegistro ? [
                'id' => $ultimoRegistro->id,
                'cantidad' => (int) $ultimoRegistro->cantidad,
                'fecha_hora' => $ultimoRegistro->fecha_hora ? $ultimoRegistro->fecha_hora->toDateTimeString() : null,
                'hora' => $ultimoRegistro->fecha_hora ? $ultimoRegistro->fecha_hora->format('H:i:s') : null,
                'fecha' => $ultimoRegistro->fecha_hora ? $ultimoRegistro->fecha_hora->format('Y-m-d') : null,
            ] : null,
            'registros' => $registros,
            'data' => $registros,
        ], 200);
    }

    /**
     * GET: Devuelve específicamente la suma total (por si se consulta endpoint directo /suma)
     */
    public function sum()
    {
        $suma = (int) PeopleCount::sum('cantidad');
        return response()->json([
            'success' => true,
            'suma' => $suma,
            'total' => $suma,
        ], 200);
    }
}

