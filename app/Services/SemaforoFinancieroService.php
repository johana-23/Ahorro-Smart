<?php

namespace App\Services;

use App\Models\User;
use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\MetaAhorro;

class SemaforoFinancieroService
{
    /**
     * RF-032: relación entre ahorro acumulado y gastos periódicos (mensuales).
     * Verde: cubre >= 3 meses de gastos | Amarillo: 1-3 meses | Rojo: < 1 mes
     */
    public function calcular(User $usuario): array
    {
        $ahorroAcumulado = MetaAhorro::where('usuario_idusuario', $usuario->id)->sum('monto_actual');

        $inicioMes = now()->startOfMonth();
        $gastoMensual = Gasto::where('usuario_idusuario', $usuario->id)
            ->where('fecha_gasto', '>=', $inicioMes)
            ->sum('monto');

        if ($gastoMensual <= 0) {
            // Sin gastos registrados aún: no se puede evaluar de forma confiable.
            return ['color' => 'amarillo', 'meses_cubiertos' => null, 'mensaje' => 'Registra tus gastos para calcular tu semáforo.'];
        }

        $mesesCubiertos = round($ahorroAcumulado / $gastoMensual, 1);

        if ($mesesCubiertos >= 3) {
            $color = 'verde';
            $mensaje = 'Tu ahorro cubre 3 o más meses de tus gastos. ¡Vas muy bien!';
        } elseif ($mesesCubiertos >= 1) {
            $color = 'amarillo';
            $mensaje = 'Tu ahorro cubre entre 1 y 3 meses de tus gastos. Sigue aportando.';
        } else {
            $color = 'rojo';
            $mensaje = 'Tu ahorro cubre menos de 1 mes de tus gastos. Es momento de priorizar el ahorro.';
        }

        return ['color' => $color, 'meses_cubiertos' => $mesesCubiertos, 'mensaje' => $mensaje];
    }
}
