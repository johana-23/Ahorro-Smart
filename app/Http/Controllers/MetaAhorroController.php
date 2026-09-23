<?php

namespace App\Http\Controllers;

use App\Http\Requests\MetaAhorroRequest;
use App\Models\MetaAhorro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MetaAhorroController extends Controller
{
    public function index()
    {
        $metas = MetaAhorro::where('usuario_idusuario', Auth::id())
            ->orderBy('estado_meta')
            ->orderByDesc('idmeta_ahorro')
            ->get();

        return view('metas.index', compact('metas'));
    }

    public function create()
    {
        return view('metas.create');
    }

    public function store(MetaAhorroRequest $request)
    {
        $data = $request->validated();
        $data['fecha_inicio'] = now();
        $data['usuario_idusuario'] = Auth::id();

        // Manejo de archivos: si el usuario subió una imagen, se guarda
        // en storage/app/public/metas y solo se persiste la ruta relativa.
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $request->file('imagen')->store('metas', 'public');
        }

        MetaAhorro::create($data);

        return redirect()->route('metas.index')->with('status', 'Meta creada correctamente.');
    }

    public function show(MetaAhorro $meta)
    {
        $this->autorizarPropietario($meta);

        return view('metas.show', compact('meta'));
    }

    /**
     * RF-013 (aporte que reduce lo que falta) y RF-033 (riega la planta).
     */
    public function aportar(Request $request, MetaAhorro $meta)
    {
        $this->autorizarPropietario($meta);

        $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
        ]);

        $meta->registrarAporte((float) $request->monto);

        return back()->with('status', '¡Aporte registrado! Tu planta de ahorro te lo agradece 🌱');
    }

    public function destroy(MetaAhorro $meta)
    {
        $this->autorizarPropietario($meta);

        if ($meta->imagen) {
            Storage::disk('public')->delete($meta->imagen);
        }

        $meta->delete();

        return redirect()->route('metas.index')->with('status', 'Meta eliminada.');
    }

    private function autorizarPropietario(MetaAhorro $meta): void
    {
        abort_if($meta->usuario_idusuario !== Auth::id(), 403);
    }
}
