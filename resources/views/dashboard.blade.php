{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
                Dashboard
            </h2>
            {{-- ❌ Eliminado botón superior para no duplicar el formulario --}}
        </div>
    </x-slot>

    @php
        // Helpers de formateo
        $dstr  = static fn($d) => optional($d)->toDateString();
        $money = static fn($n) => '$'.number_format((int)($n ?? 0), 0, ',', '.');
        $int   = static fn($n) => (int)($n ?? 0);
        // ❌ Se eliminó helper $pct porque ya no se usa (quitamos "Margen sobre ventas")
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash ok --}}
            @if (session('ok'))
                <div class="rounded-lg border border-green-700 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-3">
                    {{ session('ok') }}
                </div>
            @endif

            {{-- Filtros de fecha --}}
            <form method="GET"
                  class="flex flex-wrap items-end gap-3 bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ $dstr($desde ?? null) }}"
                           class="w-48 rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ $dstr($hasta ?? null) }}"
                           class="w-48 rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>
                <div class="ml-auto flex gap-2">
                    <button class="px-4 py-2 rounded bg-slate-700 text-white hover:bg-slate-800">Filtrar</button>
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-100">
                        Limpiar
                    </a>
                </div>
            </form>

            {{-- KPIs principales --}}
            @php
                $k = $kpis ?? [];
                $ventasTotal  = $int($k['ventas_total']  ?? 0);
                $comprasTotal = $int($k['compras_total'] ?? 0);
                $abonosTotal  = $int($k['abonos_total']  ?? 0);
                $porCobrar    = $int($k['por_cobrar']    ?? 0);
                $utilidad     = $int($k['utilidad']      ?? ($ventasTotal - $comprasTotal));
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Compras</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($comprasTotal) }}</div>
                    <div class="text-xs mt-2 text-slate-500 dark:text-slate-400">
                        Premium: {{ $money(($comprasByTipo['premium'] ?? 0)) }} ·
                        Eco: {{ $money(($comprasByTipo['eco'] ?? 0)) }}
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Ventas (facturado)</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($ventasTotal) }}</div>
                    <div class="text-xs mt-2 text-slate-500 dark:text-slate-400">
                        Premium: {{ $money(($ventasByTipo['premium'] ?? 0)) }} ·
                        Eco: {{ $money(($ventasByTipo['eco'] ?? 0)) }}
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Abonos recibidos</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($abonosTotal) }}</div>
                    <div class="text-xs mt-2 text-slate-500 dark:text-slate-400">
                        Por cobrar: <span class="font-medium">{{ $money($porCobrar) }}</span>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Utilidad bruta (Ventas - Compras)</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($utilidad) }}</div>
                </div>

                {{-- ❌ Eliminados los cuadros de "Margen sobre ventas" y "Rango" --}}
            </div>

            {{-- KPIs de Caja --}}
            @php
                $saldoInicial     = $int($k['saldo_inicial']     ?? 0);
                $ingresosCaja     = $int($k['ingresos_caja']     ?? 0);
                $egresosCaja      = $int($k['egresos_caja']      ?? 0);
                $gastosOperativos = $int($k['gastos_operativos'] ?? 0);
                $saldoFinal       = $int($k['saldo_final']       ?? ($saldoInicial + $ingresosCaja - $egresosCaja));
            @endphp

            <div class="text-right">
                <a href="{{ route('caja.index') }}"
                   class="inline-block mt-2 text-sm px-3 py-2 rounded border border-slate-300 dark:border-slate-700 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Ver movimientos de caja →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Saldo inicial (antes de {{ $dstr($desde ?? null) }})</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($saldoInicial) }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Ingresos de caja</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($ingresosCaja) }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Egresos de caja</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($egresosCaja) }}</div>
                    <div class="text-xs mt-2 text-slate-500 dark:text-slate-400">
                        Operativos: {{ $money($gastosOperativos) }}
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Saldo final</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($saldoFinal) }}</div>
                </div>

                {{-- ✅ ÚNICO botón de movimiento (abajo) --}}
                <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/50 dark:bg-black/30 p-4 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-slate-500 dark:text-slate-400 text-xs mb-1">Movimiento rápido</div>
                        <button
                            type="button"
                            class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm"
                            onclick="document.getElementById('modal-caja').showModal()">
                            + Gasto / Ingreso
                        </button>
                    </div>
                </div>
            </div>

            {{-- Resumen por tipo de grano --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Premium</h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Compras vs Ventas</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-900 p-3 border border-slate-200 dark:border-slate-800">
                            <div class="text-slate-500 dark:text-slate-400">Compras</div>
                            <div class="text-lg font-semibold">{{ $money(($comprasByTipo['premium'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-900 p-3 border border-slate-200 dark:border-slate-800">
                            <div class="text-slate-500 dark:text-slate-400">Ventas</div>
                            <div class="text-lg font-semibold">{{ $money(($ventasByTipo['premium'] ?? 0)) }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Eco</h3>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Compras vs Ventas</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-900 p-3 border border-slate-200 dark:border-slate-800">
                            <div class="text-slate-500 dark:text-slate-400">Compras</div>
                            <div class="text-lg font-semibold">{{ $money(($comprasByTipo['eco'] ?? 0)) }}</div>
                        </div>
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-900 p-3 border border-slate-200 dark:border-slate-800">
                            <div class="text-slate-500 dark:text-slate-400">Ventas</div>
                            <div class="text-lg font-semibold">{{ $money(($ventasByTipo['eco'] ?? 0)) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla: Ventas en el período --}}
            @isset($envios)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    Ventas en el período
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-left">Cliente</th>
                                <th class="p-2 text-left">Tipo</th>
                                <th class="p-2 text-left">Bultos</th>
                                <th class="p-2 text-left">Total</th>
                                <th class="p-2 text-left">Abono</th>
                                <th class="p-2 text-left">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900 dark:text-slate-100">
                            @forelse($envios as $e)
                                <tr class="{{ $loop->odd ? 'dark:bg-slate-900' : 'dark:bg-slate-950' }} hover:dark:bg-slate-800">
                                    <td class="p-2">{{ optional($e->fecha_envio ?: $e->created_at)->format('Y-m-d') }}</td>
                                    <td class="p-2">{{ $e->cliente->nombre ?? '—' }}</td>
                                    <td class="p-2 uppercase">{{ $e->tipo_grano }}</td>
                                    <td class="p-2">{{ number_format($e->numero_bulto ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-2">{{ $money($e->valor_envio) }}</td>
                                    <td class="p-2">{{ $money($e->pago_contado) }}</td>
                                    <td class="p-2">{{ $money($e->pago_a_plazo) }}</td>
                                </tr>
                            @empty
                                <tr class="dark:bg-slate-950">
                                    <td class="p-3 text-center" colspan="7">Sin ventas en el rango.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset

            {{-- Tabla: Compras en el período --}}
            @isset($compras)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    Compras en el período
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-left">Proveedor</th>
                                <th class="p-2 text-left">Tipo</th>
                                <th class="p-2 text-left">Bultos</th>
                                <th class="p-2 text-left">Precio x bulto</th>
                                <th class="p-2 text-left">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900 dark:text-slate-100">
                            @forelse($compras as $c)
                                <tr class="{{ $loop->odd ? 'dark:bg-slate-900' : 'dark:bg-slate-950' }} hover:dark:bg-slate-800">
                                    <td class="p-2">{{ optional($c->fecha_compra ?: $c->created_at)->format('Y-m-d') }}</td>
                                    <td class="p-2">{{ $c->proveedor->nombre ?? '—' }}</td>
                                    <td class="p-2 uppercase">{{ $c->tipo_grano }}</td>
                                    <td class="p-2">{{ number_format($c->cantidad_bultos ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-2">{{ $money($c->precio_por_bulto) }}</td>
                                    <td class="p-2">{{ $money($c->total) }}</td>
                                </tr>
                            @empty
                                <tr class="dark:bg-slate-950">
                                    <td class="p-3 text-center" colspan="6">Sin compras en el rango.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset

            {{-- DETALLE DE CLIENTES (agrupado) --}}
            @php
                $detalleClientes = collect($envios ?? [])
                    ->groupBy(fn($e) => $e->cliente->nombre ?? '—')
                    ->map(function($g) {
                        $bultos = (int) $g->sum('numero_bulto');
                        $ventas = (int) $g->sum('valor_envio');
                        $abono  = (int) $g->sum('pago_contado');
                        $saldo  = (int) $g->sum('pago_a_plazo');
                        return [
                            'cliente' => $g->first()->cliente->nombre ?? '—',
                            'bultos'  => $bultos,
                            'ventas'  => $ventas,
                            'abono'   => $abono,
                            'saldo'   => $saldo,
                        ];
                    })->values();
            @endphp

            @if(($detalleClientes ?? collect())->count() > 0)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    Detalle de clientes (ventas agrupadas por cliente)
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left">Cliente</th>
                                <th class="p-2 text-left">Bultos</th>
                                <th class="p-2 text-left">Ventas</th>
                                <th class="p-2 text-left">Abono</th>
                                <th class="p-2 text-left">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900 dark:text-slate-100">
                            @foreach($detalleClientes as $row)
                                <tr class="dark:bg-slate-950 hover:dark:bg-slate-800">
                                    <td class="p-2">{{ $row['cliente'] }}</td>
                                    <td class="p-2">{{ number_format($row['bultos'], 0, ',', '.') }}</td>
                                    <td class="p-2">{{ $money($row['ventas']) }}</td>
                                    <td class="p-2">{{ $money($row['abono']) }}</td>
                                    <td class="p-2">{{ $money($row['saldo']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- DETALLE DE PROVEEDORES (agrupado) --}}
            @php
                $detalleProveedores = collect($compras ?? [])
                    ->groupBy(fn($c) => $c->proveedor->nombre ?? '—')
                    ->map(function($g) {
                        $bultos = (int) $g->sum('cantidad_bultos');
                        $total  = (int) $g->sum('total');
                        $prom   = $bultos > 0 ? (int) round($total / $bultos) : 0;
                        return [
                            'proveedor'       => $g->first()->proveedor->nombre ?? '—',
                            'bultos'          => $bultos,
                            'compras_total'   => $total,
                            'precio_promedio' => $prom,
                        ];
                    })->values();
            @endphp

            @if(($detalleProveedores ?? collect())->count() > 0)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    Detalle de proveedores (compras agrupadas por proveedor)
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left">Proveedor</th>
                                <th class="p-2 text-left">Bultos</th>
                                <th class="p-2 text-left">Compras</th>
                                <th class="p-2 text-left">Precio prom. x bulto</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900 dark:text-slate-100">
                            @foreach($detalleProveedores as $row)
                                <tr class="dark:bg-slate-950 hover:dark:bg-slate-800">
                                    <td class="p-2">{{ $row['proveedor'] }}</td>
                                    <td class="p-2">{{ number_format($row['bultos'], 0, ',', '.') }}</td>
                                    <td class="p-2">{{ $money($row['compras_total']) }}</td>
                                    <td class="p-2">{{ $money($row['precio_promedio']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Abonos por cliente (tu tabla original) --}}
            @isset($abonosPorCliente)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    Abonos por cliente
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left">Cliente</th>
                                <th class="p-2 text-left">Abonado</th>
                                <th class="p-2 text-left">Por cobrar</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900 dark:text-slate-100">
                            @forelse($abonosPorCliente as $row)
                                <tr class="{{ $loop->odd ? 'dark:bg-slate-900' : 'dark:bg-slate-950' }} hover:dark:bg-slate-800">
                                    <td class="p-2">{{ $row['cliente'] ?? '—' }}</td>
                                    <td class="p-2">{{ $money($row['abonado'] ?? 0) }}</td>
                                    <td class="p-2">{{ $money($row['por_cobrar'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr class="dark:bg-slate-950">
                                    <td class="p-3 text-center" colspan="3">Sin datos de abonos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endisset

        </div>
    </div>

    {{-- MODAL (dialog): registrar gasto/ingreso de caja --}}
    <dialog id="modal-caja" class="rounded-xl p-0 w-full max-w-md">
        <div class="relative bg-white dark:bg-black text-slate-900 dark:text-slate-100 border dark:border-slate-800 rounded-xl">
            {{-- Cerrar --}}
            <form method="dialog">
                <button class="absolute right-3 top-2 text-slate-400 hover:text-slate-300" aria-label="Cerrar">✕</button>
            </form>

            {{-- Form --}}
            <form method="POST" action="{{ route('caja.store') }}" class="p-6 space-y-4">
                @csrf
                <h3 class="text-lg font-semibold">Nuevo movimiento de caja</h3>

                <div>
                    <label class="block text-sm mb-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ now()->toDateString() }}"
                           class="w-full rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Tipo</label>
                    <select name="tipo" class="w-full rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" required>
                        <option value="egreso">Egreso</option>
                        <option value="ingreso">Ingreso</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Categoría</label>
                    <select name="categoria" class="w-full rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        <option value="">-- Selecciona --</option>
                        <option value="gasolina">Gasolina</option>
                        <option value="comida">Comida</option>
                        <option value="peajes">Peajes</option>
                        <option value="otros_gastos">Otros gastos</option>
                        <option value="saldo_inicial">Saldo inicial</option>
                        <option value="venta_contado">Venta (contado)</option>
                        <option value="aporte_caja">Aporte a caja</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Monto</label>
                    <input type="number" name="monto" min="0" step="1"
                           class="w-full rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Descripción</label>
                    <input type="text" name="descripcion" maxlength="200" placeholder="Detalle opcional"
                           class="w-full rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                            class="px-4 py-2 rounded border hover:bg-slate-100 dark:hover:bg-slate-800"
                            onclick="document.getElementById('modal-caja').close()">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Guardar</button>
                </div>
            </form>
        </div>
    </dialog>
</x-app-layout>
