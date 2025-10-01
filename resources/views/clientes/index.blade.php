<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clientes</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Clientes</h1>
                <a href="{{ route('clientes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">Nuevo</a>
            </div>

            @if(session('ok'))
                <div class="bg-green-100 text-green-800 p-2 rounded mb-3">{{ session('ok') }}</div>
            @endif

            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full border">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-2 border">Nombre</th>
                            <th class="p-2 border">Documento</th>
                            <th class="p-2 border">Teléfono</th>
                            <th class="p-2 border">Email</th>
                            <th class="p-2 border">Ciudad</th>
                            <th class="p-2 border w-36">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $c)
                            <tr>
                                <td class="p-2 border">{{ $c->nombre }}</td>
                                <td class="p-2 border">{{ $c->documento }}</td>
                                <td class="p-2 border">{{ $c->telefono }}</td>
                                <td class="p-2 border">{{ $c->email }}</td>
                                <td class="p-2 border">{{ $c->ciudad }}</td>
                                <td class="p-2 border">
                                    <a href="{{ route('clientes.edit',$c) }}" class="text-indigo-600">Editar</a>
                                    <form action="{{ route('clientes.destroy',$c) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar cliente?');">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 ml-2">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-2 border text-center" colspan="6">Sin registros</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $clientes->links() }}</div>
        </div>
    </div>
</x-app-layout>
