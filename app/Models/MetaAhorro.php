<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAhorro extends Model
{
    protected $table = 'meta_ahorro';
    protected $primaryKey = 'idmeta_ahorro';
    public $timestamps = false;

    protected $fillable = [
        'nombre_meta', 'monto_actual', 'monto_objetivo',
        'fecha_inicio', 'plazo_meses', 'estado_meta', 'imagen',
        'usuario_idusuario',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'monto_actual' => 'decimal:2',
        'monto_objetivo' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }

    /**
     * Normalización (3FN): fecha_limite, progreso y ahorro_periodico NO
     * son columnas — se calculan a partir de los datos base.
     */
    public function getFechaLimiteAttribute()
    {
        return $this->fecha_inicio->copy()->addMonths($this->plazo_meses);
    }

    public function getProgresoAttribute()
    {
        if ((float) $this->monto_objetivo <= 0) {
            return 0;
        }
        return round(($this->monto_actual / $this->monto_objetivo) * 100, 2);
    }

    public function getAhorroPeriodicoAttribute()
    {
        $mesesRestantes = now()->diffInMonths($this->fecha_limite, false);
        $mesesRestantes = max($mesesRestantes, 1); // evita dividir por 0 o negativo

        $restante = $this->monto_objetivo - $this->monto_actual;

        return round(max($restante, 0) / $mesesRestantes, 2);
    }

    /**
     * RF-013 + RF-033: registra un aporte a la meta y "cuida" la planta
     * de ahorro del usuario (se llama desde MetaAhorroController@aportar).
     */
    public function registrarAporte(float $monto): void
    {
        $this->monto_actual += $monto;

        if ($this->monto_actual >= $this->monto_objetivo) {
            $this->estado_meta = 'cumplida'; // RF-028
        }

        $this->save();

        // RF-033: cualquier aporte a cualquier meta "riega" la planta
        PlantaAhorro::updateOrCreate(
            ['usuario_idusuario' => $this->usuario_idusuario],
            ['fecha_ultima_actividad' => now()]
        );
    }
}
