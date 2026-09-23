<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\MetaAhorroController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ReporteController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

    Route::resource('ingresos', IngresoController::class)->except(['show']);
    Route::resource('gastos', GastoController::class)->except(['show']);

    Route::resource('metas', MetaAhorroController::class)->except(['edit', 'update']);
    Route::post('/metas/{meta}/aportar', [MetaAhorroController::class, 'aportar'])->name('metas.aportar');

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');

    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});
