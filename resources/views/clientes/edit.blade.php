<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Cliente</h2></x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('clientes.update',$cliente) }}" class="bg-white p-6 rounded shadow space-y-3">
                @csrf @method('PUT')
                <x-text-input class="w-full" name="nombre" value="{{ old('nombre',$cliente->nombre) }}" required />
                <x-text-input class="w-full" name="documento" value="{{ old('documento',$cliente->documento) }}" />
                <x-text-input class="w-full" name="telefono" value="{{ old('telefono',$cliente->telefono) }}" />
                <x-text-input class="w-full" name="email" type="email" value="{{ old('email',$cliente->email) }}" />
                <x-text-input class="w-full" name="direccion" value="{{ old('direccion',$cliente->direccion) }}" />
                <x-text-input class="w-full" name="ciudad" value="{{ old('ciudad',$cliente->ciudad) }}" />

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('clientes.index') }}" class="px-4 py-2 border rounded">Volver</a>
                    <x-primary-button>Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
