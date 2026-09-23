<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre_categoria' => 'Arriendo', 'descripcion' => 'Pago de vivienda'],
            ['nombre_categoria' => 'Servicios públicos', 'descripcion' => 'Luz, agua, gas, internet'],
            ['nombre_categoria' => 'Alimentación', 'descripcion' => 'Mercado y comida'],
            ['nombre_categoria' => 'Transporte', 'descripcion' => 'Transporte público, gasolina'],
            ['nombre_categoria' => 'Entretenimiento', 'descripcion' => 'Salidas, streaming, ocio'],
            ['nombre_categoria' => 'Salud', 'descripcion' => 'Medicamentos, consultas'],
            ['nombre_categoria' => 'Educación', 'descripcion' => 'Matrícula, materiales, cursos'],
            ['nombre_categoria' => 'Otros', 'descripcion' => 'Gastos varios'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(['nombre_categoria' => $categoria['nombre_categoria']], $categoria);
        }
    }
}
