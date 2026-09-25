<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilRequest;
use App\Models\Perfil;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{

    public function edit()
    {
        $perfil = Perfil::where('usuario_idusuario', Auth::id())->first();

        return view('perfil.edit', compact('perfil'));
    }

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
