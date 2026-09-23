<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Ingreso;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    /**
     * RF-015: genera los datos para dos reportes gráficos:
     * 1) Ingresos vs. Gastos de los últimos 6 meses (barras)
     * 2) Distribución de gastos por categoría (torta)
     * No persiste nada: se recalcula cada vez que se visita la página.
     */
    public function index()
    {
        $userId = Auth::id();

        $meses = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        $labels = $meses->map(fn ($m) => ucfirst($m->translatedFormat('M Y')))->values();

        $ingresosPorMes = $meses->map(function ($m) use ($userId) {
            return (float) Ingreso::where('usuario_idusuario', $userId)
                ->whereYear('fecha_ingreso', $m->year)
                ->whereMonth('fecha_ingreso', $m->month)
                ->sum('monto');
        })->values();

        $gastosPorMes = $meses->map(function ($m) use ($userId) {
            return (float) Gasto::where('usuario_idusuario', $userId)
                ->whereYear('fecha_gasto', $m->year)
                ->whereMonth('fecha_gasto', $m->month)
                ->sum('monto');
        })->values();

        $gastoPorCategoria = Gasto::where('usuario_idusuario', $userId)
            ->selectRaw('categoria_idcategoria, SUM(monto) as total')
            ->groupBy('categoria_idcategoria')
            ->with('categoria')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($g) => [
                'categoria' => $g->categoria->nombre_categoria ?? 'Sin categoría',
                'total' => (float) $g->total,
            ]);

        $gastoFijoVsVariable = [
            'fijo' => (float) Gasto::where('usuario_idusuario', $userId)->fijos()->sum('monto'),
            'variable' => (float) Gasto::where('usuario_idusuario', $userId)->variables()->sum('monto'),
        ];

        return view('reportes.index', compact(
            'labels', 'ingresosPorMes', 'gastosPorMes', 'gastoPorCategoria', 'gastoFijoVsVariable'
        ));
    }
}
