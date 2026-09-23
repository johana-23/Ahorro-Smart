<?php

namespace App\Services;

use App\Models\User;

class RecomendacionService
{
    public function __construct(private AnalisisFinancieroService $analisis)
    {
    }

    /**
     * RF-031: reglas simples de negocio sobre el análisis financiero.
     * Devuelve la recomendación más relevante para el usuario.
     */
    public function generar(User $usuario): string
    {
        $a = $this->analisis->generar($usuario);

        if ($a['gastos_mes'] == 0) {
            return 'Aún no tienes gastos registrados este mes. ¡Empieza a registrar para recibir recomendaciones personalizadas!';
        }

        if ($a['porcentaje_variable'] > 40) {
            return "El {$a['porcentaje_variable']}% de tu gasto de este mes es variable (no planificado). Intenta anticipar esos gastos en tu presupuesto.";
        }

        if ($a['categoria_top'] && $a['categoria_top_monto'] > ($a['gastos_mes'] * 0.4)) {
            return "La categoría \"{$a['categoria_top']}\" concentra más del 40% de tu gasto este mes. Revisa si puedes reducirla.";
        }

        if ($a['balance_mes'] < 0) {
            return 'Tus gastos superaron tus ingresos este mes. Prioriza reducir gastos variables antes que aumentar el ahorro.';
        }

        return '¡Vas bien! Considera destinar el excedente de este mes a una de tus metas de ahorro activas.';
    }
}
