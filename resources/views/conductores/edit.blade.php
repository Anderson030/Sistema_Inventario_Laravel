{{-- resources/views/conductores/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">Editar conductor</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Usamos el ID para evitar el problema del nombre del parámetro de la ruta --}}
            <form action="{{ route('conductores.update', $conductor->id) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-black dark:text-slate-100 border dark:border-slate-800 p-6 rounded shadow space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-text-input name="nombre" value="{{ old('nombre',$conductor->nombre) }}" required
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"/>

                    <x-text-input name="apellido" value="{{ old('apellido',$conductor->apellido) }}" required
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"/>

                    <div>
                        <x-input-label value="Tipo de documento" class="dark:text-slate-200" />
                        @php $td = old('tipo_documento',$conductor->tipo_documento); @endphp
                        <select name="tipo_documento" class="w-full rounded border-gray-300 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100" required>
                            <option value="CC" @selected($td=='CC')>CC</option>
                            <option value="CE" @selected($td=='CE')>CE</option>
                            <option value="PTT" @selected($td=='PTT')>PTT</option>
                            <option value="NIT" @selected($td=='NIT')>NIT</option>
                        </select>
                    </div>

                    <x-text-input name="documento" value="{{ old('documento',$conductor->documento) }}" required
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"/>

                    <x-text-input name="celular" value="{{ old('celular',$conductor->celular) }}"
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"/>

                    <div>
                        <x-input-label value="Foto (reemplazar)" class="dark:text-slate-200" />
                        <input type="file" name="foto" accept="image/*" class="w-full rounded border-gray-300 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100">
                        @if($conductor->foto)
                            <img src="{{ asset('storage/'.$conductor->foto) }}" class="h-16 mt-2 rounded" alt="Foto actual">
                        @endif
                    </div>
                </div>

                <div>
                    <x-input-label value="Descripción (opcional)" class="dark:text-slate-200" />
                    <textarea name="descripcion" rows="3" class="w-full rounded border-gray-300 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100">{{ old('descripcion',$conductor->descripcion) }}</textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('conductores.index') }}" class="px-4 py-2 border rounded dark:border-slate-700 dark:text-slate-100 hover:bg-gray-50 dark:hover:bg-slate-800">Volver</a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
