<?php

namespace App\Http\Controllers;

use App\Models\Envio;
use App\Models\Compra;
use App\Models\Cliente;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnvioController extends Controller
{
    public function index(Request $request)
    {
        // --- Filtro de fechas (usa fecha_envio; si es null, cae a created_at) ---
        $desde = $request->date('desde');
        $hasta = $request->date('hasta');

        $dateCol = DB::raw('COALESCE(fecha_envio, created_at)');

        $q = Envio::with(['conductor', 'cliente'])->latest();

        if ($desde && $hasta) {
            $q->whereBetween($dateCol, [$desde->toDateString(), $hasta->toDateString()]);
        } elseif ($desde) {
            $q->whereDate($dateCol, '>=', $desde->toDateString());
        } elseif ($hasta) {
            $q->whereDate($dateCol, '<=', $hasta->toDateString());
        }

        $envios = $q->paginate(10)->appends($request->only('desde', 'hasta'));

        // Totales del período con el mismo criterio de fecha
        $qSum = Envio::query();
        if ($desde && $hasta) {
            $qSum->whereBetween($dateCol, [$desde->toDateString(), $hasta->toDateString()]);
        } elseif ($desde) {
            $qSum->whereDate($dateCol, '>=', $desde->toDateString());
        } elseif ($hasta) {
            $qSum->whereDate($dateCol, '<=', $hasta->toDateString());
        }

        $totalesPeriodo = [
            'vendido'  => (int) $qSum->sum('valor_envio'),   // total facturado (total vendido)
            'recibido' => (int) $qSum->sum('pago_contado'),  // abonos cobrados
        ];

        // --- Compras (arriba) ---
        $compras = Compra::with('proveedor')->latest()->paginate(10, ['*'], 'compras_page');

        // STOCK dinámico = Comprado - Vendido (por tipo)
        $compradoPremium = (int) Compra::where('tipo_grano', 'premium')->sum('cantidad_bultos');
        $compradoEco     = (int) Compra::where('tipo_grano', 'eco')->sum('cantidad_bultos');

        $vendidoPremium  = (int) Envio::where('tipo_grano', 'premium')->sum('numero_bulto');
        $vendidoEco      = (int) Envio::where('tipo_grano', 'eco')->sum('numero_bulto');

        $stock = [
            'premium' => max(0, $compradoPremium - $vendidoPremium),
            'eco'     => max(0, $compradoEco - $vendidoEco),
        ];
        $stock['total'] = $stock['premium'] + $stock['eco'];

        // Selects para modales/formularios
        $proveedores = Proveedor::orderBy('nombre')->get(['id','nombre']);
        $clientes    = Cliente::orderBy('nombre')->get(['id','nombre']);

        return view('envios.index', compact(
            'envios', 'compras', 'proveedores', 'clientes',
            'stock', 'totalesPeriodo', 'desde', 'hasta'
        ));
    }

    public function create()
    {
        // Necesario para el select de clientes en create.blade.php
        $clientes = Cliente::orderBy('nombre')->get(['id','nombre']);
        return view('envios.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        // Valida lo que capturas en el formulario "nuevo envío"
        $data = $request->validate([
            'cliente_id'   => ['required','exists:clientes,id'],
            'tipo_grano'   => ['required','in:premium,eco'],
            'numero_bulto' => ['required','integer','min:1'],
            'valor_bulto'  => ['required','string'], // llega con formato $ 1.000 -> se parsea
            'pago_contado' => ['nullable','string'], // idem
            'fecha_envio'  => ['nullable','date'],
            'fecha_plazo'  => ['nullable','date'],
        ]);

        // Parseo de dinero
        $valorBulto = $this->parseMoney($data['valor_bulto'] ?? 0);
        $abono      = $this->parseMoney($data['pago_contado'] ?? 0);

        // Cálculos
        $total = $valorBulto * (int) $data['numero_bulto'];
        $saldo = max(0, $total - $abono);

        // Inserción
        Envio::create([
            'cliente_id'    => (int) $data['cliente_id'],
            'tipo_grano'    => $data['tipo_grano'],
            'numero_bulto'  => (int) $data['numero_bulto'],
            'valor_bulto'   => $valorBulto,
            'valor_envio'   => $total,
            'pago_contado'  => $abono,
            'pago_a_plazo'  => $saldo,
            'fecha_envio'   => $data['fecha_envio'] ?? null,
            'fecha_plazo'   => $data['fecha_plazo'] ?? null,
            'estado'        => 'en_camino',
        ]);

        return redirect()->route('envios.index')->with('ok', 'Envío registrado');
    }

    public function edit(Envio $envio)
    {
        $clientes = Cliente::orderBy('nombre')->get(['id','nombre']);
        return view('envios.edit', compact('envio','clientes'));
    }

    public function update(Request $request, Envio $envio)
    {
        $data = $request->validate([
            'numero_bulto'   => ['required','integer','min:1'],
            'valor_bulto'    => ['required','string'],
            'pago_contado'   => ['nullable','string'],
            'fecha_plazo'    => ['nullable','date'],
            'cliente_id'     => ['required','exists:clientes,id'],
            'tipo_grano'     => ['required','in:premium,eco'],
            'fecha_envio'    => ['nullable','date'],
        ]);

        $valorBulto = $this->parseMoney($data['valor_bulto']);
        $abono      = $this->parseMoney($data['pago_contado'] ?? 0);
        $total      = $valorBulto * (int)$data['numero_bulto'];
        $saldo      = max(0, $total - $abono);

        $envio->update([
            'numero_bulto'   => (int)$data['numero_bulto'],
            'valor_bulto'    => $valorBulto,
            'valor_envio'    => $total,
            'pago_contado'   => $abono,
            'pago_a_plazo'   => $saldo,
            'fecha_plazo'    => $data['fecha_plazo'] ?? null,
            'fecha_envio'    => $data['fecha_envio'] ?? $envio->fecha_envio,
            'cliente_id'     => (int)$data['cliente_id'],
            'tipo_grano'     => $data['tipo_grano'],
        ]);

        return redirect()->route('envios.index')->with('ok','Envío actualizado');
    }

    public function destroy(Envio $envio)
    {
        $envio->delete();
        return redirect()->route('envios.index')->with('ok','Envío eliminado');
    }

    public function entregar(Envio $envio, Request $request)
    {
        $envio->estado = 'entregado';
        if ($request->filled('hora_llegada')) {
            $envio->hora_llegada = $request->date('hora_llegada');
        }
        $envio->save();
        return back()->with('ok', 'Entrega confirmada');
    }

    private function parseMoney($value): int
    {
        if ($value === null || $value === '') return 0;
        $digits = preg_replace('/[^\d]/', '', (string)$value);
        return (int) $digits;
    }
}
