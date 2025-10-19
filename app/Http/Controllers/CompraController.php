<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validated($request);

        // normaliza dinero
        $precio = $this->digits($data['precio_por_bulto']);
        $total  = $precio * (int)$data['cantidad_bultos'];

        Compra::create([
            'proveedor_id'      => (int)$data['proveedor_id'],
            'tipo_grano'        => $data['tipo_grano'],
            'cantidad_bultos'   => (int)$data['cantidad_bultos'],
            'precio_por_bulto'  => $precio,
            'total'             => $total,
            'fecha_compra'      => $data['fecha_compra'] ?? null,
            'observacion'       => $data['observacion'] ?? null,
        ]);

        return back()->with('ok','Compra registrada');
    }

    public function edit(Compra $compra)
    {
        $proveedores = Proveedor::orderBy('nombre')->get(['id','nombre']);
        return view('compras.edit', compact('compra','proveedores'));
    }

    public function update(Request $request, Compra $compra)
    {
        $data = $this->validated($request);

        $precio = $this->digits($data['precio_por_bulto']);
        $total  = $precio * (int)$data['cantidad_bultos'];

        $compra->update([
            'proveedor_id'      => (int)$data['proveedor_id'],
            'tipo_grano'        => $data['tipo_grano'],
            'cantidad_bultos'   => (int)$data['cantidad_bultos'],
            'precio_por_bulto'  => $precio,
            'total'             => $total,
            'fecha_compra'      => $data['fecha_compra'] ?? null,
            'observacion'       => $data['observacion'] ?? null,
        ]);

        // volvemos al index de envíos (donde está la tabla de compras arriba)
        return redirect()->route('envios.index')->with('ok','Compra actualizada');
    }

    public function destroy(Compra $compra)
    {
        $compra->delete();
        return back()->with('ok','Compra eliminada');
    }

    // ---------- helpers ----------
    private function validated(Request $request): array
    {
        return $request->validate([
            'proveedor_id'     => ['required','exists:proveedores,id'],
            'tipo_grano'       => ['required','in:premium,eco'],
            'cantidad_bultos'  => ['required','integer','min:1'],
            'precio_por_bulto' => ['required','string'], // viene con $, puntos, etc.
            'fecha_compra'     => ['nullable','date'],
            'observacion'      => ['nullable','string'],
        ]);
    }

    private function digits($v): int
    {
        return (int) preg_replace('/[^\d]/','', (string) $v);
    }
}
