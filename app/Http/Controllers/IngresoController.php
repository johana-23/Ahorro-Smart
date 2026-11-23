<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngresoRequest;
use App\Models\Ingreso;
use Illuminate\Support\Facades\Auth;

class IngresoController extends Controller
{
    public function index()
    {
        return redirect()->route('movimientos.index', ['tab' => 'ingresos']);
    }

    public function create()
    {
        return view('ingresos.create');
    }

    public function store(IngresoRequest $request)
    {
        Ingreso::create([
            ...$request->validated(),
            'usuario_idusuario' => Auth::id(),
        ]);

        return redirect()->route('movimientos.index', ['tab' => 'ingresos'])->with('status', 'Ingreso registrado correctamente.');
    }

    public function edit(Ingreso $ingreso)
    {
        $this->autorizarPropietario($ingreso);

        return view('ingresos.edit', compact('ingreso'));
    }

    public function update(IngresoRequest $request, Ingreso $ingreso)
    {
        $this->autorizarPropietario($ingreso);

        $ingreso->update($request->validated());

        return redirect()->route('movimientos.index', ['tab' => 'ingresos'])->with('status', 'Ingreso actualizado correctamente.');
    }

    public function destroy(Ingreso $ingreso)
    {
        $this->autorizarPropietario($ingreso);

        $ingreso->delete();

        return redirect()->route('movimientos.index', ['tab' => 'ingresos'])->with('status', 'Ingreso eliminado.');
    }


    private function autorizarPropietario(Ingreso $ingreso): void
    {
        abort_if($ingreso->usuario_idusuario !== Auth::id(), 403);
    }
}