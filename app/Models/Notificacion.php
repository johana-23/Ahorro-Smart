<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificacion';
    protected $primaryKey = 'idnotificacion';
    public $timestamps = false;

    protected $fillable = ['mensaje', 'tipo_notificacion', 'fecha_envio', 'estado', 'usuario_idusuario'];

    protected $casts = ['fecha_envio' => 'datetime'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }

    public function marcarLeida(): void
    {
        $this->update(['estado' => 'leida']);
    }
}
