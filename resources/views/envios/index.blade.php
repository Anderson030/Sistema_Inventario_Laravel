{{-- resources/views/envios/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Envíos</h2>
    </x-slot>

    <div class="py-6" x-data="{ show:false, envio:null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Envíos</h1>
                <a href="{{ route('envios.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Nuevo envío</a>
            </div>

            @if (session('ok'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-3">{{ session('ok') }}</div>
            @endif

            <div class="overflow-x-auto bg-white shadow-sm rounded">
                <table class="min-w-full border">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">valor envio</th>
                            <th class="p-2 border">numero bulto</th>
                            <th class="p-2 border">valor bulto</th>
                            <th class="p-2 border">ganacia total</th>
                            <th class="p-2 border">pago contado</th>
                            <th class="p-2 border">pago a plazo</th>
                            <th class="p-2 border">fecha contado</th>
                            <th class="p-2 border">fecha plazo</th>
                            <th class="p-2 border">fecha envio</th>
                            <th class="p-2 border">Estado</th>
                            <th class="p-2 border w-64">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($envios as $e)
                            <tr>
                                <td class="p-2 border">{{ $e->id }}</td>
                                <td class="p-2 border">{{ '$'.number_format($e->valor_envio,0,',','.') }}</td>
                                <td class="p-2 border">{{ $e->numero_bulto }}</td>
                                <td class="p-2 border">{{ '$'.number_format($e->valor_bulto,0,',','.') }}</td>
                                <td class="p-2 border">{{ '$'.number_format($e->ganancia_total,0,',','.') }}</td>
                                <td class="p-2 border">{{ '$'.number_format($e->pago_contado,0,',','.') }}</td>
                                <td class="p-2 border">{{ '$'.number_format($e->pago_a_plazo,0,',','.') }}</td>
                                <td class="p-2 border">{{ optional($e->fecha_contado)->format('d/m/Y') }}</td>
                                <td class="p-2 border">{{ optional($e->fecha_plazo)->format('d/m/Y') }}</td>
                                <td class="p-2 border">{{ optional($e->fecha_envio)->format('d/m/Y') }}</td>
                                <td class="p-2 border">
                                    <span class="px-2 py-1 rounded text-white text-xs {{ $e->estado==='entregado'?'bg-green-600':'bg-amber-600' }}">
                                        {{ $e->estado }}
                                    </span>
                                </td>

                                {{-- Acciones: izquierda (Ver más / Confirmar), derecha (Editar / Eliminar) --}}
                                <td class="p-2 border">
                                    <div class="flex items-center justify-between gap-2">
                                        {{-- IZQUIERDA --}}
                                        <div class="flex items-center gap-2">
                                            <button
                                                class="px-3 py-1 bg-slate-700 text-white rounded"
                                                @click="show=true; envio={{ Js::from([
                                                    'id'=>$e->id,
                                                    'conductor'=>$e->conductor?($e->conductor->nombre.' '.$e->conductor->apellido):null,
                                                    'documento'=>$e->conductor?($e->conductor->tipo_documento.' '.$e->conductor->documento):null,
                                                    'origen'=>$e->origen,
                                                    'destino'=>$e->destino,
                                                    'hora_salida'=>$e->hora_salida? $e->hora_salida->format('d/m/Y H:i') : null,
                                                    'hora_llegada'=>$e->hora_llegada? $e->hora_llegada->format('d/m/Y H:i') : null,
                                                    'estado'=>$e->estado
                                                ]) }}">
                                                Ver más
                                            </button>

                                            @if($e->estado !== 'entregado')
                                                <form action="{{ route('envios.entregar',$e) }}" method="POST"
                                                      onsubmit="return confirm('¿Confirmar entrega?');">
                                                    @csrf
                                                    {{-- Si quieres capturar hora_llegada aquí, agrega un input datetime-local con name="hora_llegada" --}}
                                                    <button class="px-3 py-1 bg-green-600 text-white rounded">
                                                        Confirmar entrega
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        {{-- DERECHA --}}
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('envios.edit',$e) }}" class="px-3 py-1 text-indigo-600">
                                                Editar
                                            </a>

                                            <form action="{{ route('envios.destroy',$e) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar este envío?');">
                                                @csrf @method('DELETE')
                                                <button class="px-3 py-1 text-red-600">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border text-center" colspan="12">Sin registros</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $envios->links() }}
            </div>

            <div class="mt-6 bg-white p-4 rounded shadow">
                <div class="text-xl">
                    <span class="font-semibold">Ganancia total acumulada:</span>
                    <span class="ml-2">{{ '$'.number_format($totalGanancia,0,',','.') }}</span>
                </div>
            </div>
        </div>

        <!-- MODAL VER MÁS -->
        <div x-cloak x-show="show" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4">
            <div @click.outside="show=false" class="bg-white w-full max-w-lg rounded shadow p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-xl font-semibold">
                        Detalle del envío #<span x-text="envio?.id"></span>
                    </h3>
                    <button class="text-gray-500" @click="show=false">✕</button>
                </div>
                <div class="space-y-2 text-sm">
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
                    <button class="px-4 py-2 bg-slate-700 text-white rounded" @click="show=false">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
