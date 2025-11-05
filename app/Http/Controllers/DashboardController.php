<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon; // ✅ usar Carbon correcto
use App\Models\Compra;
use App\Models\Envio;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\CajaMovimiento;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        // 1) Rango de fechas (por defecto: mes actual)
        $desde = $r->filled('desde') ? Carbon::parse($r->input('desde')) : now()->startOfMonth();
        $hasta = $r->filled('hasta') ? Carbon::parse($r->input('hasta')) : now()->endOfMonth();

        // Si llegan invertidas, se corrige
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        // Normalizamos para between (YYYY-mm-dd)
        $d1 = $desde->toDateString();
        $d2 = $hasta->toDateString();

        // 2) COMPRAS por tipo
        $comprasPremium = (int) Compra::whereBetween('fecha_compra', [$d1, $d2])
            ->where('tipo_grano', 'premium')
            ->sum('total');

        $comprasEco = (int) Compra::whereBetween('fecha_compra', [$d1, $d2])
            ->where('tipo_grano', 'eco')
            ->sum('total');

        $comprasByTipo = [
            'premium' => $comprasPremium,
            'eco'     => $comprasEco,
        ];
        $comprasTotal = $comprasPremium + $comprasEco;

        // 3) VENTAS (envíos como ventas reales)
        $ventasPremium = (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
            ->where('tipo_grano', 'premium')
            ->sum('valor_envio');

        $ventasEco = (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
            ->where('tipo_grano', 'eco')
            ->sum('valor_envio');

        $ventasByTipo = [
            'premium' => $ventasPremium,
            'eco'     => $ventasEco,
        ];
        $ventasTotal = $ventasPremium + $ventasEco;

        // 4) Abonos recibidos en el período
        $abonosFacturas = (int) Payment::whereBetween('paid_at', [$d1, $d2])
            ->sum('amount');

        // Abonos directos desde envíos SIN factura (pago_contado)
        $abonosEnviosSinFactura = (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
            ->whereNull('invoice_id')
            ->sum('pago_contado');

        $abonosTotal = $abonosFacturas + $abonosEnviosSinFactura;

        // 5) Por cobrar (saldo pendiente del período)
        $porCobrar = (int) Invoice::whereBetween('issue_date', [$d1, $d2])
                ->sum('balance_due')
            + (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
                ->whereNull('invoice_id')
                ->sum('pago_a_plazo');

        // 6) Utilidad bruta simple (ventas - compras)
        $utilidad = $ventasTotal - $comprasTotal;

        // 7) Listas para tablas
        $envios = Envio::with('cliente')
            ->whereBetween('fecha_envio', [$d1, $d2])
            ->orderByDesc('fecha_envio')
            ->orderByDesc('id')
            ->get();

        $compras = Compra::with('proveedor')
            ->whereBetween('fecha_compra', [$d1, $d2])
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id')
            ->get();

        // 8) Abonos por cliente
        $abonosPorCliente = [];

        // Pagos por factura
        $pagos = Payment::selectRaw('invoices.customer_name as cliente, SUM(payments.amount) as abonado')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->whereBetween('payments.paid_at', [$d1, $d2])
            ->groupBy('invoices.customer_name')
            ->get();

        foreach ($pagos as $p) {
            $abonosPorCliente[$p->cliente]['cliente']     = $p->cliente;
            $abonosPorCliente[$p->cliente]['abonado']     = ($abonosPorCliente[$p->cliente]['abonado'] ?? 0) + (int) $p->abonado;
            $abonosPorCliente[$p->cliente]['por_cobrar']  = $abonosPorCliente[$p->cliente]['por_cobrar'] ?? 0;
        }

        // Abonos directos por envío sin factura
        $pagosEnvio = Envio::selectRaw('COALESCE(clientes.nombre, "—") as cliente, SUM(envios.pago_contado) as abonado, SUM(envios.pago_a_plazo) as por_cobrar')
            ->leftJoin('clientes', 'clientes.id', '=', 'envios.cliente_id')
            ->whereBetween('envios.fecha_envio', [$d1, $d2])
            ->whereNull('envios.invoice_id')
            ->groupBy('cliente')
            ->get();

        foreach ($pagosEnvio as $p) {
            $key = $p->cliente;
            $abonosPorCliente[$key]['cliente']    = $key;
            $abonosPorCliente[$key]['abonado']    = ($abonosPorCliente[$key]['abonado'] ?? 0) + (int) $p->abonado;
            $abonosPorCliente[$key]['por_cobrar'] = ($abonosPorCliente[$key]['por_cobrar'] ?? 0) + (int) $p->por_cobrar;
        }

        // Reindex para la vista
        $abonosPorCliente = array_values($abonosPorCliente);

        /* ====== CAJA Y GASTOS ====== */

        // Saldo inicial = acumulado histórico antes del rango (ingresos - egresos)
        $saldoInicial = (int) (
            CajaMovimiento::where('fecha', '<', $d1)
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END), 0) AS saldo")
                ->value('saldo') ?? 0
        );

        // Movimientos dentro del rango
        $ingresosCaja = (int) CajaMovimiento::whereBetween('fecha', [$d1, $d2])
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $egresosCaja = (int) CajaMovimiento::whereBetween('fecha', [$d1, $d2])
            ->where('tipo', 'egreso')
            ->sum('monto');

        // Gastos operativos (si quieres mostrar aparte – ajusta categorías a las que uses)
        $gastosOperativos = (int) CajaMovimiento::whereBetween('fecha', [$d1, $d2])
            ->where('tipo', 'egreso')
            ->whereIn('categoria', ['gasolina', 'comida', 'peajes', 'otros_gastos', 'viaticos', 'flete', 'coteros'])
            ->sum('monto');

        $saldoFinal = $saldoInicial + $ingresosCaja - $egresosCaja;

        /* ====== FIN CAJA ====== */

        // KPIs para la vista
        $kpis = [
            'compras_total'     => $comprasTotal,
            'ventas_total'      => $ventasTotal,
            'abonos_total'      => $abonosTotal,
            'por_cobrar'        => $porCobrar,
            'utilidad'          => $utilidad,

            // Caja
            'saldo_inicial'     => $saldoInicial,
            'ingresos_caja'     => $ingresosCaja,
            'egresos_caja'      => $egresosCaja,
            'gastos_operativos' => $gastosOperativos,
            'saldo_final'       => $saldoFinal,
        ];

        return view('dashboard', [
            'desde'            => $desde,
            'hasta'            => $hasta,
            'kpis'             => $kpis,
            'comprasByTipo'    => $comprasByTipo,
            'ventasByTipo'     => $ventasByTipo,
            'envios'           => $envios,
            'compras'          => $compras,
            'abonosPorCliente' => $abonosPorCliente,
        ]);
    }
}
