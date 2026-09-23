<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingreso';
    protected $primaryKey = 'idingreso';
    public $timestamps = false;

    protected $fillable = ['descripcion', 'monto', 'fecha_ingreso', 'usuario_idusuario'];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'monto' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }
}
