{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    @php
        // Helpers rápidos para formateo
        $dstr = static function($d) { return optional($d)->toDateString(); };
        $money = static function($n) { return '$'.number_format((int)($n ?? 0), 0, ',', '.'); };
        $int   = static function($n) { return (int)($n ?? 0); };
        $val   = static function($arr, $key) { return $arr[$key] ?? 0; };
        $txt   = static function($arr, $key) use ($money){ return $money($arr[$key] ?? 0); };
        $pct   = static function($num, $den) {
            $den = (float)($den ?: 0);
            return $den > 0 ? number_format(($num/$den)*100, 1, ',', '.') . '%' : '—';
        };
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Margen sobre ventas</div>
                    <div class="text-2xl font-semibold mt-1">{{ $pct($utilidad, $ventasTotal) }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Rango</div>
                    <div class="mt-1 text-sm">
                        {{ $dstr($desde ?? null) ?: '—' }} &rarr; {{ $dstr($hasta ?? null) ?: '—' }}
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

            {{-- Tabla de ventas del período (Envíos) --}}
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

            {{-- Tabla de compras del período --}}
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

            {{-- Abonos por cliente (opcional) --}}
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
</x-app-layout>
