<?php

namespace App\Http\Controllers;

use App\Models\EmpresaMandante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmpresaMandanteController extends Controller
{
    public function index(): View
    {
        return view('empresas-mandantes.index', [
            'empresasMandantes' => EmpresaMandante::query()
                ->withCount(['clientes', 'importacionesCartera', 'jefesVenta'])
                ->orderBy('razon_social')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'codigo' => Str::upper(Str::squish((string) $request->input('codigo'))),
        ]);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:empresa_mandantes,codigo'],
            'razon_social' => ['required', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $datos['activo'] = $request->boolean('activo', true);
        EmpresaMandante::create($datos);

        return to_route('empresas-mandantes.index')->with('success', 'Empresa mandante registrada correctamente.');
    }

    public function seleccionar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'empresa_mandante_id' => ['required', 'integer', 'exists:empresa_mandantes,id'],
        ]);

        $empresaMandante = EmpresaMandante::query()
            ->where('activo', true)
            ->findOrFail($datos['empresa_mandante_id']);

        $request->session()->put('empresa_mandante_id', $empresaMandante->id);

        return back()->with('success', 'Empresa activa: '.$empresaMandante->razon_social.'.');
    }

    public function update(Request $request, EmpresaMandante $empresaMandante): RedirectResponse
    {
        $request->merge([
            'codigo' => Str::upper(Str::squish((string) $request->input('codigo'))),
        ]);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('empresa_mandantes', 'codigo')->ignore($empresaMandante->id)],
            'razon_social' => ['required', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
        ]);
        $datos['activo'] = $request->boolean('activo');
        $empresaMandante->update($datos);

        if (! $empresaMandante->activo && (int) $request->session()->get('empresa_mandante_id') === $empresaMandante->id) {
            $request->session()->forget('empresa_mandante_id');
        }

        return to_route('empresas-mandantes.index')->with('success', 'Empresa mandante actualizada correctamente.');
    }
}
