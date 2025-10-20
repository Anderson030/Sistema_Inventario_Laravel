<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Compra;
use App\Models\Envio;
use App\Models\Invoice;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        // 1) Rango de fechas (por defecto: mes actual)
        $desde = $r->filled('desde') ? Carbon::parse($r->input('desde')) : now()->startOfMonth();
        $hasta = $r->filled('hasta') ? Carbon::parse($r->input('hasta')) : now()->endOfMonth();

        // Normalizamos fin de día para between
        $d1 = $desde->toDateString();
        $d2 = $hasta->toDateString();

        // 2) Agregados de COMPRAS por tipo
        $comprasPremium = (int) Compra::whereBetween('fecha_compra', [$d1, $d2])
            ->where('tipo_grano', 'premium')->sum('total');

        $comprasEco = (int) Compra::whereBetween('fecha_compra', [$d1, $d2])
            ->where('tipo_grano', 'eco')->sum('total');

        $comprasByTipo = [
            'premium' => $comprasPremium,
            'eco'     => $comprasEco,
        ];
        $comprasTotal = $comprasPremium + $comprasEco;

        // 3) Agregados de VENTAS (usaremos envíos como ventas reales realizadas)
        //    Si usas facturación, puedes mezclar invoices + envíos sin invoice.
        $ventasPremium = (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
            ->where('tipo_grano', 'premium')->sum('valor_envio');

        $ventasEco = (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
            ->where('tipo_grano', 'eco')->sum('valor_envio');

        $ventasByTipo = [
            'premium' => $ventasPremium,
            'eco'     => $ventasEco,
        ];
        $ventasTotal = $ventasPremium + $ventasEco;

        // 4) Abonos recibidos en el período
        //    a) pagos por facturas
        $abonosFacturas = (int) Payment::whereBetween('paid_at', [$d1, $d2])->sum('amount');
        //    b) abonos directos desde envíos SIN factura (pago_contado)
        $abonosEnviosSinFactura = (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
            ->whereNull('invoice_id')
            ->sum('pago_contado');

        $abonosTotal = $abonosFacturas + $abonosEnviosSinFactura;

        // 5) Por cobrar (saldo pendiente de facturas del período)
        $porCobrar = (int) Invoice::whereBetween('issue_date', [$d1, $d2])->sum('balance_due')
            // + saldo de envíos sin factura (pago_a_plazo)
            + (int) Envio::whereBetween('fecha_envio', [$d1, $d2])
                ->whereNull('invoice_id')
                ->sum('pago_a_plazo');

        // 6) Utilidad bruta simple (ventas - compras)
        $utilidad = $ventasTotal - $comprasTotal;

        // 7) Listas para las tablas (ENVÍAMOS **MODELOS**, no arrays)
        $envios = Envio::with(['cliente'])
            ->whereBetween('fecha_envio', [$d1, $d2])
            ->orderByDesc('fecha_envio')
            ->get();

        $compras = Compra::with(['proveedor'])
            ->whereBetween('fecha_compra', [$d1, $d2])
            ->orderByDesc('fecha_compra')
            ->get();

        // 8) Abonos por cliente (opcional)
        //    Suma de payments por cliente (vía invoices) + pago_contado de envíos sin invoice
        $abonosPorCliente = [];
        // Pagos por factura
        $pagos = Payment::selectRaw('invoices.customer_name as cliente, SUM(payments.amount) as abonado')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->whereBetween('payments.paid_at', [$d1, $d2])
            ->groupBy('invoices.customer_name')
            ->get();

        foreach ($pagos as $p) {
            $abonosPorCliente[$p->cliente]['cliente']  = $p->cliente;
            $abonosPorCliente[$p->cliente]['abonado']  = ($abonosPorCliente[$p->cliente]['abonado'] ?? 0) + (int)$p->abonado;
            $abonosPorCliente[$p->cliente]['por_cobrar'] = $abonosPorCliente[$p->cliente]['por_cobrar'] ?? 0;
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
            $abonosPorCliente[$key]['abonado']    = ($abonosPorCliente[$key]['abonado'] ?? 0) + (int)$p->abonado;
            $abonosPorCliente[$key]['por_cobrar'] = ($abonosPorCliente[$key]['por_cobrar'] ?? 0) + (int)$p->por_cobrar;
        }

        // Reindex para la vista
        $abonosPorCliente = array_values($abonosPorCliente);

        $kpis = [
            'compras_total' => $comprasTotal,
            'ventas_total'  => $ventasTotal,
            'abonos_total'  => $abonosTotal,
            'por_cobrar'    => $porCobrar,
            'utilidad'      => $utilidad,
        ];

        return view('dashboard', [
            'desde'          => $desde,
            'hasta'          => $hasta,
            'kpis'           => $kpis,
            'comprasByTipo'  => $comprasByTipo,
            'ventasByTipo'   => $ventasByTipo,
            'envios'         => $envios,   // modelos Eloquent
            'compras'        => $compras,  // modelos Eloquent
            'abonosPorCliente' => $abonosPorCliente,
        ]);
    }
}
