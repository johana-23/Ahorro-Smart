<?php

namespace App\Http\Controllers;

use App\Http\Requests\GastoRequest;
use App\Models\Categoria;
use App\Models\Gasto;
use Illuminate\Support\Facades\Auth;

class GastoController extends Controller
{
    public function index()
    {
        $gastos = Gasto::where('usuario_idusuario', Auth::id())
            ->with('categoria')
            ->orderByDesc('fecha_gasto')
            ->paginate(10);

        return view('gastos.index', compact('gastos'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre_categoria')->get();

        return view('gastos.create', compact('categorias'));
    }

    public function store(GastoRequest $request)
    {
        $data = $request->validated();
        $data['categoria_idcategoria'] = $this->resolverCategoria($request);

        Gasto::create([
            ...$data,
            'usuario_idusuario' => Auth::id(),
        ]);

        return redirect()->route('gastos.index')->with('status', 'Gasto registrado correctamente.');
    }

    public function edit(Gasto $gasto)
    {
        $this->autorizarPropietario($gasto);
        $categorias = Categoria::orderBy('nombre_categoria')->get();

        return view('gastos.edit', compact('gasto', 'categorias'));
    }

    public function update(GastoRequest $request, Gasto $gasto)
    {
        $this->autorizarPropietario($gasto);

        $data = $request->validated();
        $data['categoria_idcategoria'] = $this->resolverCategoria($request);

        $gasto->update($data);

        return redirect()->route('gastos.index')->with('status', 'Gasto actualizado correctamente.');
    }

    public function destroy(Gasto $gasto)
    {
        $this->autorizarPropietario($gasto);
        $gasto->delete();

        return redirect()->route('gastos.index')->with('status', 'Gasto eliminado.');
    }

    /**
     * RF-009: si el usuario escribió una categoría nueva en vez de elegir
     * una existente, la crea al vuelo y la usa.
     */
    private function resolverCategoria($request): ?int
    {
        if ($request->filled('categoria_nueva')) {
            $categoria = Categoria::firstOrCreate(['nombre_categoria' => trim($request->categoria_nueva)]);
            return $categoria->idcategoria;
        }

        return $request->input('categoria_idcategoria');
    }

    private function autorizarPropietario(Gasto $gasto): void
    {
        abort_if($gasto->usuario_idusuario !== Auth::id(), 403);
    }
}
