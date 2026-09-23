<?php

namespace App\Services;

use App\Models\User;
use App\Models\Gasto;
use App\Models\Ingreso;

class AnalisisFinancieroService
{
    /**
     * RF-030: analiza los hábitos de consumo del usuario en el mes actual.
     * No persiste nada — se recalcula cada vez que se llama.
     */
    public function generar(User $usuario): array
    {
        $inicioMes = now()->startOfMonth();

        $ingresosMes = Ingreso::where('usuario_idusuario', $usuario->id)
            ->where('fecha_ingreso', '>=', $inicioMes)->sum('monto');

        $gastosMes = Gasto::where('usuario_idusuario', $usuario->id)
            ->where('fecha_gasto', '>=', $inicioMes)->sum('monto');

        $gastoFijo = Gasto::where('usuario_idusuario', $usuario->id)
            ->where('fecha_gasto', '>=', $inicioMes)->fijos()->sum('monto');

        $gastoVariable = Gasto::where('usuario_idusuario', $usuario->id)
            ->where('fecha_gasto', '>=', $inicioMes)->variables()->sum('monto');

        $categoriaTop = Gasto::where('usuario_idusuario', $usuario->id)
            ->where('fecha_gasto', '>=', $inicioMes)
            ->selectRaw('categoria_idcategoria, SUM(monto) as total')
            ->groupBy('categoria_idcategoria')
            ->orderByDesc('total')
            ->with('categoria')
            ->first();

        $porcentajeVariable = $gastosMes > 0 ? round(($gastoVariable / $gastosMes) * 100, 1) : 0;

        return [
            'ingresos_mes' => $ingresosMes,
            'gastos_mes' => $gastosMes,
            'balance_mes' => $ingresosMes - $gastosMes,
            'gasto_fijo' => $gastoFijo,
            'gasto_variable' => $gastoVariable,
            'porcentaje_variable' => $porcentajeVariable,
            'categoria_top' => $categoriaTop?->categoria?->nombre_categoria,
            'categoria_top_monto' => $categoriaTop?->total,
        ];
    }
}
