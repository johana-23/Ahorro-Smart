<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Ingreso;
use App\Models\MetaAhorro;
use App\Models\Notificacion;
use App\Models\PlantaAhorro;
use App\Services\AnalisisFinancieroService;
use App\Services\RecomendacionService;
use App\Services\SemaforoFinancieroService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(
        SemaforoFinancieroService $semaforoService,
        RecomendacionService $recomendacionService
    ) {
        $user = Auth::user();

        $planta = PlantaAhorro::firstOrCreate(
            ['usuario_idusuario' => $user->id],
            ['fecha_ultima_actividad' => now()]
        );

        $movimientos = collect()
            ->concat(Ingreso::where('usuario_idusuario', $user->id)->latest('fecha_ingreso')->take(5)->get()
                ->map(fn ($i) => ['tipo' => 'Ingreso', 'descripcion' => $i->descripcion, 'monto' => $i->monto, 'fecha' => $i->fecha_ingreso]))
            ->concat(Gasto::where('usuario_idusuario', $user->id)->latest('fecha_gasto')->take(5)->get()
                ->map(fn ($g) => ['tipo' => 'Gasto', 'descripcion' => $g->descripcion, 'monto' => $g->monto, 'fecha' => $g->fecha_gasto]))
            ->sortByDesc('fecha')
            ->take(5);

        return view('dashboard.index', [
            'perfil' => $user->perfil,
            'semaforo' => $semaforoService->calcular($user),
            'planta' => $planta,
            'metas' => MetaAhorro::where('usuario_idusuario', $user->id)
                ->where('estado_meta', 'en_progreso')->orderByDesc('idmeta_ahorro')->take(3)->get(),
            'movimientos' => $movimientos,
            'recomendacion' => $recomendacionService->generar($user),
            'notificacionesNuevas' => Notificacion::where('usuario_idusuario', $user->id)
                ->where('estado', 'no_leida')->count(),
        ]);
    }
}
