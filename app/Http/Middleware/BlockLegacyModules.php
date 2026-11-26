<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class BlockLegacyModules
{
    public function handle(Request $request, Closure $next): Response
    {
        return response('Módulo temporalmente no disponible durante la migración.', 503);
    }
}
