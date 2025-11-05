<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CajaMovimiento;
use Carbon\Carbon;

class CajaController extends Controller
{
    /**
     * Listado y totales de caja (con filtros y paginación)
     */
    public function index(Request $r)
    {
        // Rango por defecto: mes actual
        $desde = $r->filled('desde') ? Carbon::parse($r->input('desde')) : now()->startOfMonth();
        $hasta = $r->filled('hasta') ? Carbon::parse($r->input('hasta')) : now()->endOfMonth();

        $d1 = $desde->toDateString();
        $d2 = $hasta->toDateString();

        // Per-page permitido
        $allowed = [25, 50, 100, 150];
        $pp = (int) $r->input('pp', 25);
        if (!in_array($pp, $allowed, true)) {
            $pp = 25;
        }

        // Movimientos del rango (paginados)
        $movs = CajaMovimiento::whereBetween('fecha', [$d1, $d2])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($pp)
            ->appends($r->only('desde','hasta','pp'));

        // Totales del rango
        $ingresos = (int) CajaMovimiento::whereBetween('fecha', [$d1, $d2])
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $egresos = (int) CajaMovimiento::whereBetween('fecha', [$d1, $d2])
            ->where('tipo', 'egreso')
            ->sum('monto');

        // Saldo base configurable
        $saldoBase = (int) config('caja.saldo_inicial', 0);

        // Arrastre antes del rango
        $ingresosPrevios = (int) CajaMovimiento::where('fecha', '<', $d1)
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $egresosPrevios = (int) CajaMovimiento::where('fecha', '<', $d1)
            ->where('tipo', 'egreso')
            ->sum('monto');

        $saldoInicial = $saldoBase + $ingresosPrevios - $egresosPrevios;
        $saldoFinal   = $saldoInicial + $ingresos - $egresos;

        // Catálogo (para selects en la vista o edición)
        $categorias = [
            'ingreso' => ['saldo_inicial','venta_contado','aporte_caja','cobro_plazo','otros_ingresos'],
            'egreso'  => ['gasolina','comida','peajes','compra_grano','gasto_operativo','otros_gastos'],
        ];

        return view('caja.index', compact(
            'movs', 'desde', 'hasta',
            'ingresos', 'egresos',
            'saldoInicial', 'saldoFinal',
            'categorias'
        ));
    }

    /**
     * Registrar movimiento (ingreso/egreso)
     */
    public function store(Request $r)
    {
        // Normaliza monto: acepta "$3.297.000", "3.297.000", "3297000", etc.
        $r->merge(['monto' => $this->normalizeMoney($r->input('monto'))]);

        $data = $r->validate([
            'fecha'         => ['required','date'],
            'tipo'          => ['required','in:ingreso,egreso'],
            'categoria'     => ['nullable','string','max:80'],
            'descripcion'   => ['nullable','string','max:200'],
            'monto'         => ['required','integer','min:0'],
            // Enlaces opcionales
            'venta_id'      => ['nullable','integer'],
            'compra_id'     => ['nullable','integer'],
            'observaciones' => ['nullable','string'],
        ]);

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        CajaMovimiento::create($data);

        return back()->with('ok', 'Movimiento registrado en caja.');
    }

    /**
     * Formulario de edición
     */
    public function edit(CajaMovimiento $mov)
    {
        $categorias = [
            'ingreso' => ['saldo_inicial','venta_contado','aporte_caja','cobro_plazo','otros_ingresos'],
            'egreso'  => ['gasolina','comida','peajes','compra_grano','gasto_operativo','otros_gastos'],
        ];

        return view('caja.edit', compact('mov','categorias'));
    }

    /**
     * Actualizar un movimiento
     */
    public function update(Request $r, CajaMovimiento $mov)
    {
        $r->merge(['monto' => $this->normalizeMoney($r->input('monto'))]);

        $data = $r->validate([
            'fecha'         => ['required','date'],
            'tipo'          => ['required','in:ingreso,egreso'],
            'categoria'     => ['nullable','string','max:80'],
            'descripcion'   => ['nullable','string','max:200'],
            'monto'         => ['required','integer','min:0'],
            'venta_id'      => ['nullable','integer'],
            'compra_id'     => ['nullable','integer'],
            'observaciones' => ['nullable','string'],
        ]);

        $mov->update($data);

        // Preserva filtros y tamaño de página al volver
        return redirect()
            ->route('caja.index', $r->only('desde','hasta','pp'))
            ->with('ok', 'Movimiento actualizado.');
    }

    /**
     * Eliminar un movimiento
     */
    public function destroy(Request $r, CajaMovimiento $mov)
    {
        $mov->delete();

        // Preserva filtros y tamaño de página al volver
        $back = url()->previous();
        return redirect($back)->with('ok', 'Movimiento eliminado.');
    }

    /**
     * Normaliza montos de entrada: "3.297.000" -> 3297000
     */
    private function normalizeMoney(?string $raw): int
    {
        if ($raw === null) return 0;
        $num = preg_replace('/[^\d]/', '', $raw);
        return (int) ($num ?: 0);
    }
}
