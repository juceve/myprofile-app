<?php

namespace App\Http\Middleware;

use App\Models\EmpresaMandante;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompartirEmpresaMandanteActiva
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $empresasMandantes = EmpresaMandante::query()
            ->where('activo', true)
            ->orderBy('razon_social')
            ->get();
        $empresaMandanteActiva = $empresasMandantes->firstWhere('id', (int) session('empresa_mandante_id'));

        if ($empresaMandanteActiva === null) {
            session()->forget('empresa_mandante_id');
        }

        view()->share([
            'empresasMandantesActivas' => $empresasMandantes,
            'empresaMandanteActiva' => $empresaMandanteActiva,
        ]);
        $request->attributes->set('empresaMandanteActiva', $empresaMandanteActiva);

        return $next($request);
    }
}
