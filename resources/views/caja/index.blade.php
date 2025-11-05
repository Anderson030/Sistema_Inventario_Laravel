{{-- resources/views/caja/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
                Movimientos de caja
            </h2>
            <a href="{{ route('dashboard') }}"
               class="text-sm px-3 py-2 rounded border border-slate-300 dark:border-slate-700 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800">
                ← Volver al dashboard
            </a>
        </div>
    </x-slot>

    @php
        $dstr  = static fn($d) => optional($d)->toDateString();
        $money = static fn($n) => '$'.number_format((int)($n ?? 0), 0, ',', '.');
        $badge = static function($tipo) {
            return $tipo === 'ingreso'
                ? 'bg-green-600/15 text-green-600 border border-green-600/30'
                : 'bg-rose-600/15 text-rose-600 border border-rose-600/30';
        };

        // Tamaño de página seleccionado
        $pp = (int) request('pp', $movs->perPage() ?? 25);

        // Query string común para preservar filtros/pp en Editar/Eliminar/Paginación
        $qs = array_filter([
            'desde' => request('desde'),
            'hasta' => request('hasta'),
            'pp'    => $pp,
        ], fn($v) => !is_null($v) && $v !== '');
        $qsStr = $qs ? ('?'.http_build_query($qs)) : '';

        // Para numeración (#) considerando paginación
        $rowStart = ($movs->currentPage() - 1) * $movs->perPage();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash --}}
            @if (session('ok'))
                <div class="rounded-lg border border-green-700 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-3">
                    {{ session('ok') }}
                </div>
            @endif

            {{-- Filtros + per-page --}}
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

                <div>
                    <label class="block text-sm text-slate-600 dark:text-slate-300 mb-1">Registros por página</label>
                    <select name="pp"
                            class="w-40 rounded border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        @foreach([25,50,100,150] as $opt)
                            <option value="{{ $opt }}" {{ (int)$pp === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ml-auto flex gap-2">
                    <button class="px-4 py-2 rounded bg-slate-700 text-white hover:bg-slate-800">Filtrar</button>
                    <a href="{{ route('caja.index') }}"
                       class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 dark:text-slate-100">
                        Limpiar
                    </a>
                    <button type="button"
                            class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700"
                            onclick="document.getElementById('modal-caja').showModal()">
                        + Gasto / Ingreso
                    </button>
                </div>
            </form>

            {{-- KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Saldo inicial</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($saldoInicial) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Ingresos</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($ingresos) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Egresos</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($egresos) }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black p-4">
                    <div class="text-slate-500 dark:text-slate-400 text-sm">Saldo final</div>
                    <div class="text-2xl font-semibold mt-1">{{ $money($saldoFinal) }}</div>
                </div>
            </div>

            {{-- Tabla de movimientos --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
                <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    Detalle de movimientos ({{ $dstr($desde) }} → {{ $dstr($hasta) }})
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left w-16">#</th>
                                <th class="p-2 text-left">Fecha</th>
                                <th class="p-2 text-left">Tipo</th>
                                <th class="p-2 text-left">Categoría</th>
                                <th class="p-2 text-left">Descripción</th>
                                <th class="p-2 text-right">Monto</th>
                                <th class="p-2 text-right w-48">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-900 dark:text-slate-100">
                            @forelse($movs as $m)
                                <tr class="{{ $loop->odd ? 'dark:bg-slate-900' : 'dark:bg-slate-950' }} hover:dark:bg-slate-800">
                                    <td class="p-2">{{ $rowStart + $loop->iteration }}</td>
                                    <td class="p-2">{{ optional($m->fecha ?: $m->created_at)->format('Y-m-d') }}</td>
                                    <td class="p-2">
                                        <span class="px-2 py-0.5 rounded text-xs {{ $badge($m->tipo) }}">
                                            {{ strtoupper($m->tipo) }}
                                        </span>
                                    </td>
                                    <td class="p-2">{{ $m->categoria ?: '—' }}</td>
                                    <td class="p-2">{{ $m->descripcion ?: '—' }}</td>
                                    <td class="p-2 text-right">{{ $money($m->monto) }}</td>
                                    <td class="p-2">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('caja.edit', $m).$qsStr }}"
                                               class="px-3 py-1 rounded bg-black text-indigo-400 hover:bg-slate-800">
                                                Editar
                                            </a>
                                            <form action="{{ route('caja.destroy', $m).$qsStr }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar este movimiento?');"
                                                  class="inline">
                                                @csrf @method('DELETE')
                                                <button class="px-3 py-1 rounded bg-black text-red-400 hover:bg-slate-800">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="dark:bg-slate-950">
                                    <td class="p-3 text-center" colspan="7">Sin movimientos en el rango.</td>
                                </tr>
                            @endforelse
                        </tbody>

                        @php
                            $totIng = $movs->getCollection()->where('tipo','ingreso')->sum('monto');
                            $totEgr = $movs->getCollection()->where('tipo','egreso')->sum('monto');
                            $neto   = $totIng - $totEgr;
                        @endphp
                        <tfoot class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-100">
                            <tr>
                                <th class="p-2 text-left" colspan="5">Ingresos (página)</th>
                                <th class="p-2 text-right">{{ $money($totIng) }}</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th class="p-2 text-left" colspan="5">Egresos (página)</th>
                                <th class="p-2 text-right">{{ $money($totEgr) }}</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th class="p-2 text-left" colspan="5">Neto (página)</th>
                                <th class="p-2 text-right">{{ $money($neto) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-3">
                    {{-- Resumen "mostrando X–Y de Z" --}}
                    <div class="text-sm text-slate-600 dark:text-slate-300">
                        Mostrando
                        <strong>{{ $movs->firstItem() }}</strong>–<strong>{{ $movs->lastItem() }}</strong>
                        de <strong>{{ $movs->total() }}</strong> movimientos
                    </div>

                    {{-- Paginación preservando filtros y pp --}}
                    <div class="ml-auto">
                        {{ $movs->appends($qs)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: registrar movimiento --}}
    <dialog id="modal-caja" class="rounded-xl p-0 w-full max-w-md">
        <div class="relative bg-white dark:bg-black text-slate-900 dark:text-slate-100 border dark:border-slate-800 rounded-xl">
            <form method="dialog">
                <button class="absolute right-3 top-2 text-slate-400 hover:text-slate-300" aria-label="Cerrar">✕</button>
            </form>

            <form method="POST" action="{{ route('caja.store') }}" class="p-6 space-y-4" id="form-nuevo-mov">
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
                    <input type="text" name="monto" id="monto-nuevo" placeholder="$0"
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

    {{-- Formato de dinero para el modal --}}
    <script>
      (function(){
        const inp = document.getElementById('monto-nuevo');
        if(!inp) return;
        const digits = s => (s||'').replace(/[^\d]/g,'');
        const fmt    = n => '$' + (Number(n)||0).toLocaleString('es-CO');
        function moneyInput(e){
            const d = digits(e.target.value);
            e.target.value = d ? fmt(d) : '';
        }
        inp.addEventListener('input', moneyInput);
        document.getElementById('form-nuevo-mov').addEventListener('submit', ()=>{
            inp.value = digits(inp.value); // enviar solo dígitos
        });
      })();
    </script>
</x-app-layout>
