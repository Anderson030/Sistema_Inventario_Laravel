{{-- resources/views/envios/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">Envíos</h2>
    </x-slot>

    <div class="py-6" x-data="{ show:false, envio:null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- =========================
                 SECCIÓN COMPRAS (ARRIBA)
            ========================== --}}
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Compras</h1>
                <button
                    type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                    onclick="document.getElementById('modal-compra').showModal()">
                    Nueva compra
                </button>
            </div>

            {{-- Tabla de compras --}}
            <div class="overflow-x-auto bg-white dark:bg-slate-900 border dark:border-slate-800 shadow-sm rounded mt-4 mb-6">
                <table class="min-w-full border dark:border-slate-800">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="p-2 border dark:border-slate-800">ID</th>
                            <th class="p-2 border dark:border-slate-800">Proveedor</th>
                            <th class="p-2 border dark:border-slate-800">Tipo</th>
                            <th class="p-2 border dark:border-slate-800">Bultos comprados</th>
                            <th class="p-2 border dark:border-slate-800">Precio por bulto</th>
                            <th class="p-2 border dark:border-slate-800">Total</th>
                            <th class="p-2 border dark:border-slate-800">Fecha</th>
                            <th class="p-2 border dark:border-slate-800">Obs.</th>
                            <th class="p-2 border dark:border-slate-800 w-40">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compras as $c)
                            <tr>
                                <td class="p-2 border dark:border-slate-800">{{ $c->id }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ $c->proveedor->nombre ?? '—' }}</td>
                                <td class="p-2 border dark:border-slate-800 uppercase">{{ $c->tipo_grano }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ number_format($c->cantidad_bultos,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800">${{ number_format($c->precio_por_bulto,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800 font-semibold">${{ number_format($c->total,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ optional($c->fecha_compra)->format('d/m/Y') }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ $c->observacion }}</td>
                                <td class="p-2 border dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('compras.edit', $c) }}"
                                           class="px-3 py-1 rounded bg-black text-indigo-400 hover:bg-slate-800">
                                            Editar
                                        </a>
                                        <form action="{{ route('compras.destroy', $c) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar esta compra?');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1 rounded bg-black text-red-400 hover:bg-slate-800">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border dark:border-slate-800 text-center" colspan="9">Sin compras registradas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Resumen de stock --}}
            <div class="bg-white dark:bg-black border dark:border-slate-800 rounded-lg shadow px-5 py-4 mb-8">
                <h4 class="font-semibold mb-3">Existencias</h4>
                <div class="grid sm:grid-cols-3 gap-4 text-sm">
                    <div class="rounded-lg border dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-3">
                        <div class="text-slate-500 dark:text-slate-400">Existencia total de granos</div>
                        <div class="text-xl font-bold">{{ number_format($stock['total'] ?? 0,0,',','.') }}</div>
                    </div>
                    <div class="rounded-lg border dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-3">
                        <div class="text-slate-500 dark:text-slate-400">Premium</div>
                        <div class="text-xl font-bold">{{ number_format($stock['premium'] ?? 0,0,',','.') }}</div>
                    </div>
                    <div class="rounded-lg border dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-3">
                        <div class="text-slate-500 dark:text-slate-400">Eco</div>
                        <div class="text-xl font-bold">{{ number_format($stock['eco'] ?? 0,0,',','.') }}</div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                {{ $compras->onEachSide(1)->links() }}
            </div>

            {{-- Modal NUEVA COMPRA --}}
            <dialog id="modal-compra" class="rounded-xl p-0 w-full max-w-xl">
                <div class="relative bg-white dark:bg-black text-slate-900 dark:text-slate-100 border dark:border-slate-800 rounded-xl">
                    <form method="dialog">
                        <button class="absolute right-3 top-2 text-slate-400 hover:text-slate-300" aria-label="Cerrar">✕</button>
                    </form>

                    <form method="POST" action="{{ route('compras.store') }}" class="p-6 space-y-4">
                        @csrf
                        <h3 class="text-lg font-semibold">Registrar compra</h3>

                        <div>
                            <label class="block text-sm mb-1">Proveedor</label>
                            <select name="proveedor_id" class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                                <option value="">Seleccione…</option>
                                @foreach($proveedores as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            @error('proveedor_id') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Tipo de grano</label>
                            <select name="tipo_grano" class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                                <option value="premium">Premium</option>
                                <option value="eco">Eco</option>
                            </select>
                            @error('tipo_grano') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Bultos comprados</label>
                                <input type="number" min="1" name="cantidad_bultos" id="c_cantidad"
                                       class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                                @error('cantidad_bultos') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Precio por bulto</label>
                                <input type="text" name="precio_por_bulto" id="c_precio"
                                       class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" placeholder="$0" required>
                                @error('precio_por_bulto') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Fecha de compra</label>
                                <input type="date" name="fecha_compra" value="{{ now()->toDateString() }}"
                                       class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Total</label>
                                <input type="text" id="c_total" class="w-full border rounded px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Observación (opcional)</label>
                            <textarea name="observacion" rows="2" class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" placeholder="Notas…"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" class="px-4 py-2 rounded border hover:bg-slate-100 dark:hover:bg-slate-800" onclick="document.getElementById('modal-compra').close()">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Guardar</button>
                        </div>
                    </form>
                </div>
            </dialog>

            {{-- =========================
                 SECCIÓN ENVÍOS (ABAJO)
            ========================== --}}
            <div class="flex items-center justify-between mb-2 mt-10">
                <h1 class="text-2xl font-bold">Envíos</h1>

                @isset($clientes)
                    <button type="button"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                            onclick="document.getElementById('modal-envio').showModal()">
                        Nuevo envío
                    </button>
                @else
                    <a href="{{ route('envios.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Nuevo envío</a>
                @endisset
            </div>

            {{-- Filtro por fechas + totales del período --}}
            <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ optional($desde)->toDateString() }}" class="border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ optional($hasta)->toDateString() }}" class="border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100">
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-slate-700 text-white rounded">Filtrar</button>
                    <a href="{{ route('envios.index') }}" class="px-4 py-2 border rounded hover:bg-slate-100 dark:hover:bg-slate-800">Limpiar</a>
                </div>

                <div class="ml-auto text-sm bg-white dark:bg-slate-900 border dark:border-slate-800 rounded shadow px-4 py-2">
                    <div><b>Total vendido:</b> ${{ number_format($totalesPeriodo['vendido'] ?? 0,0,',','.') }}</div>
                    <div><b>Total recibido (abonos):</b> ${{ number_format($totalesPeriodo['recibido'] ?? 0,0,',','.') }}</div>
                </div>
            </form>

            <div class="overflow-x-auto bg-white dark:bg-slate-900 border dark:border-slate-800 shadow-sm rounded mt-8">
                <table class="min-w-full border dark:border-slate-800">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="p-2 border dark:border-slate-800">ID</th>
                            <th class="p-2 border dark:border-slate-800">Fecha envío</th>
                            <th class="p-2 border dark:border-slate-800">Cliente</th>
                            <th class="p-2 border dark:border-slate-800">Tipo</th>
                            <th class="p-2 border dark:border-slate-800">Bultos</th>
                            <th class="p-2 border dark:border-slate-800">Valor por bulto</th>
                            <th class="p-2 border dark:border-slate-800">Total</th>
                            <th class="p-2 border dark:border-slate-800">Abono</th>
                            <th class="p-2 border dark:border-slate-800">Queda debiendo</th>
                            <th class="p-2 border dark:border-slate-800">Fecha plazo</th>
                            <th class="p-2 border dark:border-slate-800">Estado</th>
                            <th class="p-2 border dark:border-slate-800 w-80">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($envios as $e)
                            @php $hasInvoice = isset($e->invoice_id) && $e->invoice_id; @endphp
                            <tr>
                                <td class="p-2 border dark:border-slate-800">{{ $e->id }}</td>
                                <td class="p-2 border dark:border-slate-800">
                                    @php $f = $e->fecha_envio ?: $e->created_at; @endphp
                                    {{ optional($f)->format('d/m/Y') }}
                                </td>
                                <td class="p-2 border dark:border-slate-800">{{ optional($e->cliente)->nombre ?? '—' }}</td>
                                <td class="p-2 border dark:border-slate-800 uppercase">{{ $e->tipo_grano }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ $e->numero_bulto }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ '$'.number_format($e->valor_bulto,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ '$'.number_format($e->valor_envio,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ '$'.number_format($e->pago_contado,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ '$'.number_format($e->pago_a_plazo,0,',','.') }}</td>
                                <td class="p-2 border dark:border-slate-800">{{ optional($e->fecha_plazo)->format('d/m/Y') }}</td>
                                <td class="p-2 border dark:border-slate-800">
                                    <span class="px-2 py-1 rounded text-white text-xs {{ $e->estado==='entregado'?'bg-green-600':'bg-amber-600' }}">
                                        {{ $e->estado }}
                                    </span>
                                </td>
                                <td class="p-2 border dark:border-slate-800">
                                    <div class="flex items-center justify-between gap-2">
                                        {{-- Acciones visibles: Editar / Eliminar --}}
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('envios.edit',$e) }}"
                                               class="px-3 py-1 rounded bg-black text-indigo-400 hover:bg-slate-800">
                                                Editar
                                            </a>
                                            <form action="{{ route('envios.destroy',$e) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar este envío?');" class="inline">
                                                @csrf @method('DELETE')
                                                <button class="px-3 py-1 rounded bg-black text-red-400 hover:bg-slate-800">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Menú ⋯: teletransportado al <body> con posición fija y drop-up si no hay espacio --}}
                                        <div class="relative" 
                                             x-data="{
                                                open:false,
                                                w:224,          // ancho del menú
                                                x:0, y:0,       // coordenadas base del botón
                                                openUp:false,   // decide si se abre hacia arriba
                                                place(btn){
                                                  const r = btn.getBoundingClientRect();
                                                  this.x = Math.min(r.right - this.w, window.innerWidth - this.w - 8);
                                                  const spaceBelow = window.innerHeight - r.bottom;
                                                  this.openUp = spaceBelow < 160; // si hay poco espacio, abre hacia arriba
                                                  this.y = this.openUp ? r.top - 8 : r.bottom + 8;
                                                }
                                             }"
                                             @keydown.escape.window="open=false">
                                            <button @click="place($el); open = !open"
                                                    class="px-2 py-1 rounded bg-slate-700 text-white hover:bg-slate-800"
                                                    aria-haspopup="menu" :aria-expanded="open">
                                                ⋯
                                            </button>

                                            {{-- El menú vive en body para no ser recortado por overflow --}}
                                            <template x-teleport="body">
                                                <div x-cloak x-show="open"
                                                     @click.outside="open=false"
                                                     x-transition
                                                     :style="`position:fixed; z-index:1000; width:${w}px; left:${x}px; ${openUp ? 'top:'+y+'px; transform:translateY(-100%);' : 'top:'+y+'px;'} `"
                                                     class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-black shadow-xl">
                                                    <div class="py-1 text-sm text-slate-700 dark:text-slate-200">
                                                        {{-- Ver más (modal) --}}
                                                        <button
                                                            class="w-full text-left px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800"
                                                            @click="
                                                                open=false;
                                                                show=true;
                                                                envio={{ Js::from([
                                                                    'id'          => $e->id,
                                                                    'cliente'     => optional($e->cliente)->nombre,
                                                                    'tipo'        => $e->tipo_grano,
                                                                    'bultos'      => $e->numero_bulto,
                                                                    'valor_bulto' => '$'.number_format($e->valor_bulto,0,',','.'),
                                                                    'total'       => '$'.number_format($e->valor_envio,0,',','.'),
                                                                    'abono'       => '$'.number_format($e->pago_contado,0,',','.'),
                                                                    'saldo'       => '$'.number_format($e->pago_a_plazo,0,',','.'),
                                                                    'fecha_envio' => ($e->fecha_envio ?: $e->created_at)?->format('d/m/Y'),
                                                                    'fecha_plazo' => optional($e->fecha_plazo)->format('d/m/Y'),
                                                                    'conductor'   => $e->conductor?($e->conductor->nombre.' '.$e->conductor->apellido):null,
                                                                    'documento'   => $e->conductor?($e->conductor->tipo_documento.' '.$e->conductor->documento):null,
                                                                    'origen'      => $e->origen,
                                                                    'destino'     => $e->destino,
                                                                    'hora_salida' => $e->hora_salida? $e->hora_salida->format('d/m/Y H:i') : null,
                                                                    'hora_llegada'=> $e->hora_llegada? $e->hora_llegada->format('d/m/Y H:i') : null,
                                                                    'estado'      => $e->estado
                                                                ]) }}
                                                            ">
                                                            Ver más
                                                        </button>

                                                        @if($e->estado !== 'entregado')
                                                            <form action="{{ route('envios.entregar',$e) }}" method="POST"
                                                                  onsubmit="return confirm('¿Confirmar entrega?');">
                                                                @csrf
                                                                <button class="w-full text-left px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">
                                                                    Confirmar entrega
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if($hasInvoice)
                                                            <a href="{{ route('invoices.show', $e->invoice_id) }}"
                                                               class="block px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">
                                                                Ver factura
                                                            </a>
                                                            <a href="{{ route('invoices.pdf', $e->invoice_id) }}"
                                                               class="block px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">
                                                                Descargar PDF
                                                            </a>
                                                        @else
                                                            <a class="block px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800"
                                                               href="{{ route('facturacion.buscar', ['envio_id' => $e->id]) }}">
                                                                Facturar
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border dark:border-slate-800 text-center" colspan="12">Sin registros</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $envios->links() }}
            </div>
        </div>

        {{-- MODAL VER MÁS --}}
        <div x-cloak x-show="show" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
            <div @click.outside="show=false"
                 class="bg-white dark:bg-black text-slate-900 dark:text-slate-100 border dark:border-slate-800 w-full max-w-lg rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-xl font-semibold">
                        Detalle del envío #<span x-text="envio?.id"></span>
                    </h3>
                    <button class="text-gray-500 dark:text-slate-300 hover:opacity-80" @click="show=false">✕</button>
                </div>
                <div class="space-y-2 text-sm">
                    <p><b>Cliente:</b> <span x-text="envio?.cliente ?? '—'"></span></p>
                    <p><b>Tipo:</b> <span x-text="envio?.tipo ?? '—'"></span></p>
                    <p><b>Bultos:</b> <span x-text="envio?.bultos ?? '—'"></span></p>
                    <p><b>Valor bulto:</b> <span x-text="envio?.valor_bulto ?? '—'"></span></p>
                    <p><b>Total:</b> <span x-text="envio?.total ?? '—'"></span></p>
                    <p><b>Abono:</b> <span x-text="envio?.abono ?? '—'"></span></p>
                    <p><b>Saldo:</b> <span x-text="envio?.saldo ?? '—'"></span></p>
                    <p><b>Fecha envío:</b> <span x-text="envio?.fecha_envio ?? '—'"></span></p>
                    <p><b>Fecha plazo:</b> <span x-text="envio?.fecha_plazo ?? '—'"></span></p>
                    <hr class="my-2">
                    <p><b>Conductor:</b> <span x-text="envio?.conductor ?? '—'"></span></p>
                    <p><b>Documento:</b> <span x-text="envio?.documento ?? '—'"></span></p>
                    <p><b>Origen:</b> <span x-text="envio?.origen ?? '—'"></span></p>
                    <p><b>Destino:</b> <span x-text="envio?.destino ?? '—'"></span></p>
                    <p><b>Hora salida:</b> <span x-text="envio?.hora_salida ?? '—'"></span></p>
                    <p><b>Hora llegada:</b> <span x-text="envio?.hora_llegada ?? '—'"></span></p>
                    <p>
                        <b>Estado:</b>
                        <span class="px-2 py-1 rounded text-white"
                              :class="envio?.estado==='entregado'?'bg-green-600':'bg-amber-600'"
                              x-text="envio?.estado"></span>
                    </p>
                </div>
                <div class="mt-4 text-right">
                    <button class="px-4 py-2 bg-slate-700 text-white rounded hover:bg-slate-800" @click="show=false">Cerrar</button>
                </div>
            </div>
        </div>

        {{-- MODAL NUEVO ENVÍO --}}
        @isset($clientes)
        <dialog id="modal-envio" class="rounded-xl p-0 w-full max-w-3xl">
            <div class="relative bg-white dark:bg-black text-slate-900 dark:text-slate-100 border dark:border-slate-800 rounded-xl">
                <form method="dialog">
                    <button class="absolute right-3 top-2 text-slate-400 hover:text-slate-300" aria-label="Cerrar">✕</button>
                </form>

                <form method="POST" action="{{ route('envios.store') }}" class="p-6 space-y-4">
                    @csrf
                    <h3 class="text-lg font-semibold">Nuevo envío</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Cliente</label>
                            <select name="cliente_id" class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                                <option value="">Seleccione…</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Tipo de grano</label>
                            <select name="tipo_grano" class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                                <option value="premium">Premium</option>
                                <option value="eco">Eco</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Bultos a vender</label>
                            <input type="number" min="1" name="numero_bulto" id="e_bultos"
                                   class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Valor por bulto</label>
                            <input type="text" name="valor_bulto" id="e_valor_bulto"
                                   class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" placeholder="$0" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Total</label>
                            <input type="text" name="valor_envio" id="e_total"
                                   class="w-full border rounded px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" readonly>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Abono</label>
                            <input type="text" name="pago_contado" id="e_abono"
                                   class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" placeholder="$0" required>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Queda debiendo</label>
                            <input type="text" name="pago_a_plazo" id="e_saldo"
                                   class="w-full border rounded px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100" readonly>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Fecha de envío</label>
                            <input type="date" name="fecha_envio" value="{{ now()->toDateString() }}"
                                   class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Fecha de plazo (opcional)</label>
                            <input type="date" name="fecha_plazo"
                                   class="w-full border rounded px-3 py-2 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100">
                        </div>
                        <div class="self-end text-right">
                            <button type="button" class="px-4 py-2 rounded border hover:bg-slate-100 dark:hover:bg-slate-800"
                                    onclick="document.getElementById('modal-envio').close()">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </dialog>
        @endisset
    </div>

    {{-- Script: total compra en modal (precio * cantidad) --}}
    <script>
    (function() {
      const precio = document.getElementById('c_precio');
      const cant   = document.getElementById('c_cantidad');
      const total  = document.getElementById('c_total');

      function onlyDigits(s){ return (s||'').replace(/[^\d]/g,''); }
      function recalc(){
        const p = Number(onlyDigits(precio?.value));
        const c = Number(cant?.value || 0);
        if (total) total.value = '$' + (p * c).toLocaleString('es-CO');
      }
      function formatMoneyInput(e){
        const digits = onlyDigits(e.target.value);
        e.target.value = digits ? '$' + Number(digits).toLocaleString('es-CO') : '';
        recalc();
      }
      precio?.addEventListener('input', formatMoneyInput);
      cant?.addEventListener('input', recalc);
    })();

    // Cálculos del modal "Nuevo envío"
    (function() {
      const fmt = n => '$' + (Number(n)||0).toLocaleString('es-CO');
      const digits = s => (s||'').replace(/[^\d]/g,'');

      const bultos = document.getElementById('e_bultos');
      const vb     = document.getElementById('e_valor_bulto');
      const total  = document.getElementById('e_total');
      const abono  = document.getElementById('e_abono');
      const saldo  = document.getElementById('e_saldo');

      function recalcEnvio(){
        const nb = Number(bultos?.value || 0);
        const pv = Number(digits(vb?.value));
        const tot = nb * pv;
        if (total) total.value = fmt(tot);
        const ab = Number(digits(abono?.value));
        if (saldo) saldo.value = fmt(Math.max(tot - ab, 0));
      }
      function moneyInput(e){
        e.target.value = digits(e.target.value) ? fmt(digits(e.target.value)) : '';
        recalcEnvio();
      }
      vb?.addEventListener('input', moneyInput);
      abono?.addEventListener('input', moneyInput);
      bultos?.addEventListener('input', recalcEnvio);
    })();

    // Mantener posición de scroll al refrescar
    (function() {
      const key = 'scrollY:' + location.pathname + location.search;
      const y = localStorage.getItem(key);
      if (y !== null) { setTimeout(() => { window.scrollTo(0, parseInt(y, 10) || 0); }, 0); }
      window.addEventListener('beforeunload', () => {
        localStorage.setItem(key, String(window.scrollY || window.pageYOffset || 0));
      });
    })();
    </script>
</x-app-layout>
