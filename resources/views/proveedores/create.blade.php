{{-- resources/views/proveedores/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
            Nuevo Proveedor
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 rounded border border-red-800/40 bg-red-900/20 text-red-200 px-4 py-3">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('proveedores.store') }}"
                  class="bg-white dark:bg-black dark:text-slate-100 border dark:border-slate-800 p-6 md:p-8 rounded-2xl shadow-lg space-y-5">
                @csrf

                {{-- Nombre --}}
                <div class="space-y-1">
                    <x-input-label value="Nombre" class="dark:text-slate-200"/>
                    <x-text-input
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                        name="nombre"
                        placeholder="Nombre"
                        required />
                </div>

                {{-- RUT (mantiene name=documento) --}}
                <div class="space-y-1">
                    <x-input-label value="RUT" class="dark:text-slate-200"/>
                    <x-text-input
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                        name="documento"
                        placeholder="RUT" />
                    <p class="text-xs text-slate-400 mt-1">
                        * Para facturación, ingresa el RUT del proveedor.
                    </p>
                </div>

                {{-- Teléfono --}}
                <div class="space-y-1">
                    <x-input-label value="Teléfono" class="dark:text-slate-200"/>
                    <x-text-input
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                        name="telefono"
                        placeholder="Teléfono" />
                </div>

                {{-- Email --}}
                <div class="space-y-1">
                    <x-input-label value="Email" class="dark:text-slate-200"/>
                    <x-text-input
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                        name="email"
                        type="email"
                        placeholder="Email" />
                </div>

                {{-- Dirección --}}
                <div class="space-y-1">
                    <x-input-label value="Dirección" class="dark:text-slate-200"/>
                    <x-text-input
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                        name="direccion"
                        placeholder="Dirección" />
                </div>

                {{-- Ciudad --}}
                <div class="space-y-1">
                    <x-input-label value="Ciudad" class="dark:text-slate-200"/>
                    <x-text-input
                        class="w-full dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100"
                        name="ciudad"
                        placeholder="Ciudad" />
                </div>

                {{-- Acciones --}}
                <div class="flex gap-3 justify-end pt-2">
                    <a href="{{ route('proveedores.index') }}"
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
