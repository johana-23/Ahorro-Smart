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
        'dinero_bolsillo',
        'usuario_idusuario',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }
}
