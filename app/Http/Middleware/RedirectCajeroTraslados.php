<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCajeroTraslados
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->hasRole('cajero')) {
            // Si es la ruta de gestión (web), redirigir al historial
            if ($request->routeIs('inventario.traslados_almacen.index')) {
                return redirect()->route('inventario.traslados_almacen.historial');
            }

            if ($request->routeIs('inventario.traslados_tiendas.index')) {
                return redirect()->route('inventario.traslados_tiendas.historial');
            }

            // Si es una petición API o una acción de gestión
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. No tienes permisos para realizar acciones de gestión.'
                ], 403);
            }
        }

        return $next($request);
    }
}
