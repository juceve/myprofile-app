<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Deuda;
use App\Models\ImportacionCartera;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class CarteraController extends Controller
{
    /**
     * Muestra las obligaciones importadas desde la cartera externa.
     */
    public function index(Request $request): View
    {
        $empresaMandante = $request->attributes->get('empresaMandanteActiva');
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:255'],
            'vendedor' => ['nullable', 'string', 'max:255'],
            'supervisor' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:10'],
            'situacion' => ['nullable', 'string', 'in:vigente,cancelada_por_mandante'],
        ]);

        $deudas = Deuda::query()
            ->with('cliente')
            ->where('estado_operativo', $filtros['situacion'] ?? 'vigente')
            ->when($empresaMandante, fn ($query) => $query->whereHas('cliente', fn ($query) => $query->where('empresa_mandante_id', $empresaMandante->id)))
            ->when(! $empresaMandante, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($filtros['buscar'] ?? null, function ($query, string $buscar): void {
                $query->where(function ($query) use ($buscar): void {
                    $query->whereLike('numero_documento', "%{$buscar}%")
                        ->orWhereHas('cliente', function ($query) use ($buscar): void {
                            $query->whereLike('nombre', "%{$buscar}%")
                                ->orWhereLike('codigo_externo', "%{$buscar}%")
                                ->orWhereLike('telefono', "%{$buscar}%");
                        });
                });
            })
            ->when($filtros['ciudad'] ?? null, fn ($query, string $ciudad) => $query->whereHas('cliente', fn ($query) => $query->where('ciudad', $ciudad)))
            ->when($filtros['vendedor'] ?? null, fn ($query, string $vendedor) => $query->where('vendedor_nombre', $vendedor))
            ->when($filtros['supervisor'] ?? null, fn ($query, string $supervisor) => $query->where('supervisor_nombre', $supervisor))
            ->when($filtros['estado'] ?? null, fn ($query, string $estado) => $query->where('estado_origen', $estado));

        $resumen = [
            'deudas' => (clone $deudas)->count(),
            'clientes' => (clone $deudas)->distinct('cliente_id')->count('cliente_id'),
            'saldo' => (float) (clone $deudas)->sum('saldo_actual'),
        ];

        return view('cartera.index', [
            'deudas' => $deudas->orderBy('fecha_vencimiento')->orderBy('id')->paginate(25)->withQueryString(),
            'resumen' => $resumen,
            'ciudades' => Cliente::query()->when($empresaMandante, fn ($query) => $query->where('empresa_mandante_id', $empresaMandante->id))->whereHas('deudas')->whereNotNull('ciudad')->distinct()->orderBy('ciudad')->pluck('ciudad'),
            'vendedores' => Deuda::query()->whereNotNull('vendedor_nombre')->whereHas('cliente', fn ($query) => $query->where('empresa_mandante_id', $empresaMandante?->id))->distinct()->orderBy('vendedor_nombre')->pluck('vendedor_nombre'),
            'supervisores' => Deuda::query()->whereNotNull('supervisor_nombre')->whereHas('cliente', fn ($query) => $query->where('empresa_mandante_id', $empresaMandante?->id))->distinct()->orderBy('supervisor_nombre')->pluck('supervisor_nombre'),
            'estados' => Deuda::query()->whereNotNull('estado_origen')->whereHas('cliente', fn ($query) => $query->where('empresa_mandante_id', $empresaMandante?->id))->distinct()->orderBy('estado_origen')->pluck('estado_origen'),
            'importaciones' => ImportacionCartera::query()->when($empresaMandante, fn ($query) => $query->where('empresa_mandante_id', $empresaMandante->id))->when(! $empresaMandante, fn ($query) => $query->whereRaw('1 = 0'))->orderByDesc('procesado_en')->orderByDesc('id')->limit(5)->get(),
            'empresaMandante' => $empresaMandante,
        ]);
    }

    public function importar(Request $request): RedirectResponse
    {
        $empresaMandante = $request->attributes->get('empresaMandanteActiva');

        if ($empresaMandante === null) {
            return back()->with('error', 'Seleccione una empresa mandante activa antes de importar una cartera.');
        }

        $validated = $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        try {
            $ruta = $validated['archivo']->storeAs('importaciones', $validated['archivo']->getClientOriginalName(), 'local');
            $resultado = Artisan::call('cartera:importar', [
                'archivo' => Storage::disk('local')->path($ruta),
                '--empresa' => $empresaMandante->codigo,
            ]);
            $mensaje = trim(Artisan::output());

            if ($resultado !== 0) {
                return back()->with('error', $mensaje !== '' ? $mensaje : 'No se pudo importar la cartera.');
            }

            return redirect()->route('cartera.index')->with('success', 'La cartera se importó correctamente para '.$empresaMandante->codigo.' - '.$empresaMandante->razon_social.'.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', $exception->getMessage());
        }
    }
}
