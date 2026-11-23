<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'gastos' ? 'gastos' : 'ingresos';

        $ingresos = Ingreso::where('usuario_idusuario', Auth::id())
            ->orderByDesc('fecha_ingreso')
            ->paginate(10, ['*'], 'ingresos_page')
            ->appends(['tab' => 'ingresos']);

        $gastos = Gasto::where('usuario_idusuario', Auth::id())
            ->with('categoria')
            ->orderByDesc('fecha_gasto')
            ->paginate(10, ['*'], 'gastos_page')
            ->appends(['tab' => 'gastos']);

        return view('movimientos.index', compact('ingresos', 'gastos', 'tab'));
    }
}