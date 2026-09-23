<?php

use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\MetaAhorro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {

    // GET /api/metas -> lista las metas del usuario autenticado en JSON
    Route::get('/metas', function (Request $request) {
        return MetaAhorro::where('usuario_idusuario', $request->user()->id)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->idmeta_ahorro,
                'nombre' => $m->nombre_meta,
                'monto_actual' => $m->monto_actual,
                'monto_objetivo' => $m->monto_objetivo,
                'progreso' => $m->progreso,
                'estado' => $m->estado_meta,
            ]);
    });

    // GET /api/resumen -> resumen rápido de ingresos/gastos del mes (JSON)
    Route::get('/resumen', function (Request $request) {
        $userId = $request->user()->id;
        $inicioMes = now()->startOfMonth();

        return response()->json([
            'ingresos_mes' => Ingreso::where('usuario_idusuario', $userId)->where('fecha_ingreso', '>=', $inicioMes)->sum('monto'),
            'gastos_mes' => Gasto::where('usuario_idusuario', $userId)->where('fecha_gasto', '>=', $inicioMes)->sum('monto'),
        ]);
    });
});