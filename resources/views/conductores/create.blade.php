{{-- resources/views/conductores/create.blade.php --}}
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo conductor</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('conductores.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-text-input name="nombre" placeholder="Nombre" required />
                    <x-text-input name="apellido" placeholder="Apellido" required />

                    <div>
                        <x-input-label value="Tipo de documento" />
                        <select name="tipo_documento" class="w-full rounded border-gray-300" required>
                            <option value="">-- Selecciona --</option>
                            <option value="CC">CC</option>
                            <option value="CE">CE</option>
                            <option value="PTT">PTT</option>
                            <option value="NIT">NIT</option>
                        </select>
                    </div>

                    <x-text-input name="documento" placeholder="Documento" required />
                    <x-text-input name="celular" placeholder="Celular" />

                    <div>
                        <x-input-label value="Foto" />
                        <input type="file" name="foto" accept="image/*" class="w-full rounded border-gray-300">
                        <p class="text-xs text-gray-500 mt-1">Se almacena en <code>storage/app/public/conductores</code>. Ejecuta <b>php artisan storage:link</b> una vez.</p>
                    </div>
                </div>

                <div>
                    <x-input-label value="Descripción (opcional)" />
                    <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300"></textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('conductores.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
