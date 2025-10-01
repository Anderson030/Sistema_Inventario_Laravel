{{-- resources/views/conductores/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar conductor</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('conductores.update', $conductor) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-text-input name="nombre" value="{{ old('nombre',$conductor->nombre) }}" required />
                    <x-text-input name="apellido" value="{{ old('apellido',$conductor->apellido) }}" required />

                    <div>
                        <x-input-label value="Tipo de documento" />
                        @php $td = old('tipo_documento',$conductor->tipo_documento); @endphp
                        <select name="tipo_documento" class="w-full rounded border-gray-300" required>
                            <option value="CC" @selected($td=='CC')>CC</option>
                            <option value="CE" @selected($td=='CE')>CE</option>
                            <option value="PTT" @selected($td=='PTT')>PTT</option>
                            <option value="NIT" @selected($td=='NIT')>NIT</option>
                        </select>
                    </div>

                    <x-text-input name="documento" value="{{ old('documento',$conductor->documento) }}" required />
                    <x-text-input name="celular" value="{{ old('celular',$conductor->celular) }}" />

                    <div>
                        <x-input-label value="Foto (reemplazar)" />
                        <input type="file" name="foto" accept="image/*" class="w-full rounded border-gray-300">
                        @if($conductor->foto)
                            <img src="{{ asset('storage/'.$conductor->foto) }}" class="h-16 mt-2 rounded" alt="Foto actual">
                        @endif
                    </div>
                </div>

                <div>
                    <x-input-label value="Descripción (opcional)" />
                    <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300">{{ old('descripcion',$conductor->descripcion) }}</textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('conductores.index') }}" class="px-4 py-2 border rounded">Volver</a>
                    <x-primary-button>Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
