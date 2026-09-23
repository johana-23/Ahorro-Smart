<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilRequest;
use App\Models\Perfil;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    /**
     * RF-005 / RF-006: muestra el formulario. Si el usuario ya tiene
     * perfil, lo trae para editarlo; si no, el formulario aparece vacío
     * (se crea al guardar por primera vez).
     */
    public function edit()
    {
        $perfil = Perfil::where('usuario_idusuario', Auth::id())->first();

        return view('perfil.edit', compact('perfil'));
    }

    /**
     * updateOrCreate: si no existe perfil para este usuario, lo crea
     * (RF-005); si ya existe, lo actualiza (RF-006). Un solo método
     * cubre los dos requisitos porque el perfil es 1 a 1 con el usuario.
     */
    public function update(PerfilRequest $request)
    {
        Perfil::updateOrCreate(
            ['usuario_idusuario' => Auth::id()],
            $request->validated()
        );

        return redirect()
            ->route('perfil.edit')
            ->with('status', 'Perfil actualizado correctamente.');
    }
}
