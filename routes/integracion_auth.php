<?php
use App\Http\Controllers\IntegrationAuthController;
use Illuminate\Support\Facades\Route;

// SOLO para validacion aislada. Cargar desde routes/web.php al aprobar las pruebas.
Route::prefix('integracion')->name('integracion.')->group(function () {
    Route::get('/login', fn () => view('integracion.login'))->name('login.form');
    Route::post('/login', [IntegrationAuthController::class, 'login'])->name('login');
    Route::get('/register', fn () => view('integracion.register'))->name('register.form');
    Route::post('/register', [IntegrationAuthController::class, 'register'])->name('register');
    Route::get('/me', [IntegrationAuthController::class, 'me'])->name('me');
    Route::post('/logout', [IntegrationAuthController::class, 'logout'])->name('logout');
});
