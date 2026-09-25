<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $primaryKey = 'idperfil';
    public $timestamps = false;


    protected $fillable = [
        'ingresos_aproximados',
        'gastos_mensuales',
        'usuario_idusuario',
    ];

    protected $casts = [
        'ingresos_aproximados' => 'decimal:2',
        'gastos_mensuales' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }


    public function getDineroBolsilloAttribute(): float
    {
        return round((float) $this->ingresos_aproximados - (float) $this->gastos_mensuales, 2);
    }

    public function getSaldoActualAttribute(): float
    {
        $totalIngresos = Ingreso::where('usuario_idusuario', $this->usuario_idusuario)->sum('monto');
        $totalGastos = Gasto::where('usuario_idusuario', $this->usuario_idusuario)->sum('monto');

        return round($this->dinero_bolsillo + (float) $totalIngresos - (float) $totalGastos, 2);
    }
}
