{{-- resources/views/conductores/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
            Nuevo conductor
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 rounded border border-red-800/40 bg-red-900/20 text-red-200 px-4 py-3">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('conductores.store') }}" method="POST" enctype="multipart/form-data"
                  class="bg-white dark:bg-black dark:text-slate-100 border dark:border-slate-800 p-6 md:p-8 rounded-2xl shadow-lg space-y-6">
                @csrf

                {{-- Datos básicos --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-text-input name="nombre" placeholder="Nombre" required
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-600"/>

                    <x-text-input name="apellido" placeholder="Apellido" required
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-600"/>

                    <div>
                        <x-input-label value="Tipo de documento" class="dark:text-slate-200 mb-1"/>
                        <select name="tipo_documento"
                                class="w-full rounded border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-600"
                                required>
                            <option value="">-- Selecciona --</option>
                            <option value="CC">CC</option>
                            <option value="CE">CE</option>
                            <option value="PTT">PTT</option>
                            <option value="NIT">NIT</option>
                        </select>
                    </div>

                    <x-text-input name="documento" placeholder="Documento" required
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-600"/>

                    <x-text-input name="celular" placeholder="Celular"
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-600"/>
                </div>

                {{-- Foto --}}
                <div class="space-y-2">
                    <x-input-label value="Foto" class="dark:text-slate-200"/>
                    <input
                        type="file"
                        name="foto"
                        accept="image/*"
                        class="w-full rounded border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100
                               file:mr-4 file:rounded file:border-0 file:px-4 file:py-2
                               file:bg-slate-800 file:text-slate-100 hover:file:bg-slate-700 file:cursor-pointer">
                    <p class="text-xs text-gray-500 dark:text-slate-400">
                        Se almacena en <code class="text-xs">storage/app/public/conductores</code>.
                        Ejecuta <b>php artisan storage:link</b> una vez.
                    </p>
                </div>

                {{-- Descripción --}}
                <div class="space-y-2">
                    <x-input-label value="Descripción (opcional)" class="dark:text-slate-200"/>
                    <textarea name="descripcion" rows="4"
                              class="w-full rounded border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-600"></textarea>
                </div>

                {{-- Acciones --}}
                <div class="flex gap-3 justify-end pt-2">
                    <a href="{{ route('conductores.index') }}"
                       class="px-4 py-2 rounded border border-gray-300 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 dark:text-slate-100">
                        Cancelar
                    </a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        Guardar
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
