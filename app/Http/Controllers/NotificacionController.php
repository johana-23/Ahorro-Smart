<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = Notificacion::where('usuario_idusuario', Auth::id())
            ->orderByDesc('fecha_envio')
            ->paginate(15);

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function marcarLeida(Notificacion $notificacion)
    {
        abort_if($notificacion->usuario_idusuario !== Auth::id(), 403);

        $notificacion->marcarLeida();

        return back();
    }
}
