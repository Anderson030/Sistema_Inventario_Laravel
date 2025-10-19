{{-- resources/views/conductores/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">Conductores</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('ok'))
                <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-3">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg md:text-xl font-semibold dark:text-slate-100">Listado</h3>
                <a href="{{ route('conductores.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                    Nuevo conductor
                </a>
            </div>

            @if($conductores->count() === 0)
                <div class="bg-white dark:bg-slate-900 dark:text-slate-100 border dark:border-slate-800 shadow rounded p-6 text-center text-gray-500 dark:text-slate-300">
                    Sin conductores aún.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-7">
                    @foreach ($conductores as $c)
                        <div class="bg-black text-white border border-slate-800 shadow-lg rounded-xl overflow-hidden">
                            {{-- Imagen (alto fijo) --}}
                            <div class="h-48 bg-gray-100 dark:bg-slate-800 overflow-hidden">
                                @php
                                    $fotoUrl = $c->foto
                                        ? asset('storage/'.$c->foto)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($c->nombre.' '.$c->apellido) . '&background=111827&color=ffffff';
                                @endphp
                                <img src="{{ $fotoUrl }}" alt="Foto de {{ $c->nombre }}" class="w-full h-full object-cover">
                            </div>

                            <div class="p-5 md:p-6 space-y-1.5">
                                <div class="text-xl font-semibold">{{ $c->nombre }} {{ $c->apellido }}</div>
                                <div class="text-sm text-slate-300">
                                    {{ strtoupper($c->tipo_documento) }}:
                                    <span class="font-medium text-slate-100">{{ $c->documento }}</span>
                                </div>
                                @if($c->celular)
                                    <div class="text-sm text-slate-300">
                                        Celular:
                                        <a href="tel:{{ $c->celular }}" class="text-indigo-400 hover:text-indigo-300 underline-offset-2 hover:underline">
                                            {{ $c->celular }}
                                        </a>
                                    </div>
                                @endif
                                @if($c->descripcion)
                                    <div class="text-sm text-slate-200/90 line-clamp-2">{{ $c->descripcion }}</div>
                                @endif
                            </div>

                            <div class="px-5 md:px-6 pb-5 md:pb-6 flex items-center gap-3">
                                {{-- Asignar envíos (modal) --}}
                                <div x-data="{ open:false }" class="flex-1">
                                    <button @click="open=true"
                                            class="w-full px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded">
                                        Asignar envíos
                                    </button>

                                    {{-- Modal asignación (oscuro) --}}
                                    <div x-cloak x-show="open"
                                         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                                        <div @click.outside="open=false"
                                             class="w-full max-w-lg rounded-xl shadow-xl p-6 space-y-4 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border dark:border-slate-800">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-lg font-semibold">
                                                    Asignar envío a {{ $c->nombre }}
                                                </h4>
                                                <button class="text-slate-500 hover:text-slate-300" @click="open=false">✕</button>
                                            </div>

                                            <form action="{{ route('conductores.asignar', $c) }}" method="POST" class="space-y-3">
                                                @csrf

                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Envío sin asignar</label>
                                                    <select name="envio_id"
                                                            class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                                                            required>
                                                        <option value="">-- Selecciona --</option>
                                                        @forelse($enviosNoAsignados as $e)
                                                            <option value="{{ $e->id }}">
                                                                {{ $e->cliente?->nombre }} — #{{ $e->id }}
                                                            </option>
                                                        @empty
                                                            <option value="">No hay envíos disponibles</option>
                                                        @endforelse
                                                    </select>
                                                    <p class="text-xs text-slate-400 mt-1">
                                                        * Formato: <b>Nombre del cliente — #ID</b>
                                                    </p>
                                                </div>

                                                <div class="grid sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-medium mb-1">De (origen)</label>
                                                        <input type="text" name="origen"
                                                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                                                               placeholder="Bodega A" required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium mb-1">Para (destino)</label>
                                                        <input type="text" name="destino"
                                                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                                                               placeholder="Cliente B" required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium mb-1">Hora salida</label>
                                                        <input type="datetime-local" name="hora_salida"
                                                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                                                               required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium mb-1">Hora llegada (aprox)</label>
                                                        <input type="datetime-local" name="hora_llegada"
                                                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-end gap-2 pt-2">
                                                    <button type="button" @click="open=false"
                                                            class="px-3 py-2 rounded border hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                                        Cancelar
                                                    </button>
                                                    <button class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                                        Asignar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('conductores.edit', $c) }}"
                                   class="px-3 py-2.5 rounded border border-slate-700 text-slate-200 hover:bg-slate-800">
                                    Editar
                                </a>

                                <form action="{{ route('conductores.destroy', $c) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar conductor?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-2.5 rounded bg-red-600 hover:bg-red-700 text-white">
                                        Eliminar
                                    </button>
                                </form>
                            </div>

                            <div class="px-5 md:px-6 pb-4 text-xs text-slate-400">
                                Envíos asignados: {{ $c->envios_count }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $conductores->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
