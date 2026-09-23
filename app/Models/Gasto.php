<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gasto';
    protected $primaryKey = 'idgasto';
    public $timestamps = false;

    protected $fillable = [
        'descripcion', 'monto', 'fecha_gasto', 'tipo_gasto',
        'usuario_idusuario', 'categoria_idcategoria',
    ];

    protected $casts = [
        'fecha_gasto' => 'date',
        'monto' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_idusuario');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_idcategoria');
    }

    public function scopeFijos($query)
    {
        return $query->where('tipo_gasto', 'fijo');
    }

    public function scopeVariables($query)
    {
        return $query->where('tipo_gasto', 'variable');
    }
}
