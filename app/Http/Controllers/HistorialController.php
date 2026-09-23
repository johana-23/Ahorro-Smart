<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $tipo = $request->input('tipo', 'todos'); // todos | ingreso | gasto

        $movimientos = collect();

        if ($tipo !== 'gasto') {
            $q = Ingreso::where('usuario_idusuario', $userId);
            if ($desde) $q->where('fecha_ingreso', '>=', $desde);
            if ($hasta) $q->where('fecha_ingreso', '<=', $hasta);

            $movimientos = $movimientos->concat(
                $q->get()->map(fn ($i) => [
                    'tipo' => 'Ingreso',
                    'descripcion' => $i->descripcion,
                    'monto' => (float) $i->monto,
                    'fecha' => $i->fecha_ingreso,
                    'extra' => null,
                ])
            );
        }

        if ($tipo !== 'ingreso') {
            $q = Gasto::where('usuario_idusuario', $userId)->with('categoria');
            if ($desde) $q->where('fecha_gasto', '>=', $desde);
            if ($hasta) $q->where('fecha_gasto', '<=', $hasta);

            $movimientos = $movimientos->concat(
                $q->get()->map(fn ($g) => [
                    'tipo' => 'Gasto',
                    'descripcion' => $g->descripcion,
                    'monto' => (float) $g->monto,
                    'fecha' => $g->fecha_gasto,
                    'extra' => ($g->categoria->nombre_categoria ?? 'Sin categoría') . ' · ' . ucfirst($g->tipo_gasto),
                ])
            );
        }

        $movimientos = $movimientos->sortByDesc('fecha')->values();

        // Totales del rango filtrado, útiles para el resumen superior
        $totalIngresos = $movimientos->where('tipo', 'Ingreso')->sum('monto');
        $totalGastos = $movimientos->where('tipo', 'Gasto')->sum('monto');

        return view('historial.index', compact('movimientos', 'totalIngresos', 'totalGastos', 'desde', 'hasta', 'tipo'));
    }
}
