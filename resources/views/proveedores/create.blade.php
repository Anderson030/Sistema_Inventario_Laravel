<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Proveedor</h2></x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('proveedores.store') }}" class="bg-white p-6 rounded shadow space-y-3">
                @csrf
                <x-text-input class="w-full" name="nombre" placeholder="Nombre" required />
                <x-text-input class="w-full" name="documento" placeholder="Documento" />
                <x-text-input class="w-full" name="telefono" placeholder="Teléfono" />
                <x-text-input class="w-full" name="email" type="email" placeholder="Email" />
                <x-text-input class="w-full" name="direccion" placeholder="Dirección" />
                <x-text-input class="w-full" name="ciudad" placeholder="Ciudad" />

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('proveedores.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
