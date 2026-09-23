<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categoria';
    protected $primaryKey = 'idcategoria';
    public $timestamps = false;

    protected $fillable = ['nombre_categoria', 'descripcion'];

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'categoria_idcategoria');
    }
}
