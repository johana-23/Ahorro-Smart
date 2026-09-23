<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantaAhorro extends Model
{
    protected $table = 'planta_ahorro';
    protected $primaryKey = 'idplanta';
    public $timestamps = false;

    protected $fillable = ['fecha_ultima_actividad', 'usuario_idusuario'];

    protected $casts = ['fecha_ultima_actividad' => 'datetime'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }

   
    public function getEstadoPlantaAttribute(): string
    {
        $horas = $this->fecha_ultima_actividad->diffInHours(now());

        if ($horas < 48) {
            return 'floreciendo';
        }
        if ($horas < 72) {
            return 'marchitandose';
        }
        return 'necesita_agua';
    }

    public function getEmojiAttribute(): string
    {
        return match ($this->estado_planta) {
            'floreciendo' => '🌱',
            'marchitandose' => '🥀',
            default => '🍂',
        };
    }

    public function getMensajeAttribute(): string
    {
        return match ($this->estado_planta) {
            'floreciendo' => '¡Tu planta está floreciendo! Sigue así.',
            'marchitandose' => 'Tu planta se está marchitando, hazle un aporte.',
            default => 'Tu planta necesita agua urgente — cuídala hoy.',
        };
    }
}
