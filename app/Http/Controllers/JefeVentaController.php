<?php

namespace App\Http\Controllers;

use App\Models\JefeVenta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JefeVentaController extends Controller
{
    public function index(Request $request): View
    {
        $empresaMandante = $request->attributes->get('empresaMandanteActiva');
        $buscar = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ])['buscar'] ?? null;

        $jefesVenta = JefeVenta::query()
            ->when($empresaMandante, fn ($query) => $query->where('empresa_mandante_id', $empresaMandante->id))
            ->when(! $empresaMandante, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($buscar, fn ($query, string $valor) => $query->whereLike('nombre', "%{$valor}%"))
            ->orderBy('nombre')
            ->get();

        return view('jefes-venta.index', compact('jefesVenta', 'empresaMandante', 'buscar'));
    }
}
