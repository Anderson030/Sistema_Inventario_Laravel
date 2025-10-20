{{-- resources/views/proveedores/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">Proveedores</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold dark:text-slate-100">Proveedores</h1>
                <a href="{{ route('proveedores.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Nuevo
                </a>
            </div>

            @if(session('ok'))
                <div class="bg-green-100 dark:bg-emerald-900/20 text-green-800 dark:text-emerald-200 border border-emerald-200/30 dark:border-emerald-800/40 p-2 rounded mb-3">
                    {{ session('ok') }}
                </div>
            @endif

            <div class="overflow-x-auto bg-white dark:bg-black border border-slate-200 dark:border-slate-800 shadow rounded">
                <table class="min-w-full border-collapse">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                        <tr>
                            <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Nombre</th>
                            <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">RUT</th> {{-- antes: Documento --}}
                            <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Teléfono</th>
                            <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Email</th>
                            <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Ciudad</th>
                            <th class="p-2 border border-slate-200 dark:border-slate-800 w-36 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-900 dark:text-slate-100">
                        @forelse($proveedores as $p)
                            <tr class="{{ $loop->odd ? 'dark:bg-slate-900' : 'dark:bg-slate-950' }} hover:dark:bg-slate-800 transition-colors">
                                <td class="p-2 border border-slate-200 dark:border-slate-800">{{ $p->nombre }}</td>
                                <td class="p-2 border border-slate-200 dark:border-slate-800">{{ $p->documento }}</td> {{-- mostramos como RUT --}}
                                <td class="p-2 border border-slate-200 dark:border-slate-800">{{ $p->telefono }}</td>
                                <td class="p-2 border border-slate-200 dark:border-slate-800">{{ $p->email }}</td>
                                <td class="p-2 border border-slate-200 dark:border-slate-800">{{ $p->ciudad }}</td>
                                <td class="p-2 border border-slate-200 dark:border-slate-800">
                                    <div class="flex items-center gap-2">
                                        {{-- Editar (usar ID para evitar {proveedore}) --}}
                                        <a href="{{ route('proveedores.edit', $p->id) }}"
                                           class="px-3 py-1 rounded bg-black text-indigo-400 hover:bg-slate-800">
                                            Editar
                                        </a>
                                        {{-- Eliminar (usar ID para evitar {proveedore}) --}}
                                        <form action="{{ route('proveedores.destroy', $p->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar proveedor?');">
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
                                <td class="p-2 border border-slate-200 dark:border-slate-800 text-center" colspan="6">
                                    Sin registros
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $proveedores->links() }}</div>
        </div>
    </div>
</x-app-layout>
