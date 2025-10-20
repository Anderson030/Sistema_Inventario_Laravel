{{-- resources/views/envios/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
            Editar envío #{{ $envio->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 rounded border border-red-800/40 bg-red-900/20 text-red-200 px-4 py-3">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('envios.update',$envio) }}"
                  class="bg-white dark:bg-black dark:text-slate-100 border border-slate-200 dark:border-slate-800 p-6 md:p-8 rounded-2xl shadow-lg space-y-6"
                  id="f-envio-edit">
                @csrf
                @method('PUT')

                {{-- Cliente --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Cliente</label>
                    <select name="cliente_id"
                            class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                            required>
                        <option value="">Seleccione…</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ $envio->cliente_id == $c->id ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Bultos / Valor por bulto / Total (readonly) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Bultos a vender</label>
                        <input type="number" min="1" name="numero_bulto" id="e_bultos"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                               value="{{ $envio->numero_bulto }}" required>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Valor por bulto</label>
                        <input type="text" name="valor_bulto" id="e_valor_bulto"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                               value="{{ '$'.number_format($envio->valor_bulto,0,',','.') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Total</label>
                        <input type="text" id="e_total"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3 py-2"
                               readonly>
                    </div>
                </div>

                {{-- Tipo de grano --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Tipo de grano</label>
                    <select name="tipo_grano"
                            class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                            required>
                        <option value="premium" {{ $envio->tipo_grano === 'premium' ? 'selected' : '' }}>Premium</option>
                        <option value="eco"     {{ $envio->tipo_grano === 'eco' ? 'selected' : '' }}>Eco</option>
                    </select>
                </div>

                {{-- Abono / Saldo / Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Abono</label>
                        <input type="text" name="pago_contado" id="e_abono"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                               value="{{ '$'.number_format($envio->pago_contado,0,',','.') }}" placeholder="$0">
                    </div>
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Queda debiendo</label>
                        <input type="text" id="e_saldo"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-3 py-2"
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Fecha de envío</label>
                        <input type="date" name="fecha_envio"
                               value="{{ optional($envio->fecha_envio ?? $envio->created_at)->format('Y-m-d') }}"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600">
                    </div>
                    <div>
                        <label class="block text-sm mb-1 dark:text-slate-300">Fecha de plazo</label>
                        <input type="date" name="fecha_plazo"
                               value="{{ optional($envio->fecha_plazo)->format('Y-m-d') }}"
                               class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600">
                    </div>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('envios.index') }}"
                       class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Volver
                    </a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cálculos en vivo: total y saldo --}}
    <script>
    (function(){
        const digits = s => (s||'').replace(/[^\d]/g,'');
        const fmt    = n => Number(n||0).toLocaleString('es-CO');

        const bultos = document.getElementById('e_bultos');
        const valor  = document.getElementById('e_valor_bulto');
        const total  = document.getElementById('e_total');
        const abono  = document.getElementById('e_abono');
        const saldo  = document.getElementById('e_saldo');

        function recalc(){
            const nb = Number(bultos?.value || 0);
            const vb = Number(digits(valor?.value));
            const ab = Number(digits(abono?.value));
            const tt = nb * vb;
            const sd = Math.max(0, tt - ab);
            if (total) total.value = '$' + fmt(tt);
            if (saldo) saldo.value = '$' + fmt(sd);
        }

        function moneyInput(e){
            const v = digits(e.target.value);
            e.target.value = v ? '$' + Number(v).toLocaleString('es-CO') : '';
            recalc();
        }

        valor?.addEventListener('input', moneyInput);
        abono?.addEventListener('input', moneyInput);
        bultos?.addEventListener('input', recalc);

        window.addEventListener('DOMContentLoaded', recalc);

        document.getElementById('f-envio-edit').addEventListener('submit', ()=>{
            if (valor) valor.value = digits(valor.value);
            if (abono) abono.value = digits(abono.value);
        });
    })();
    </script>
</x-app-layout>
