{{-- resources/views/caja/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
                Editar movimiento de caja #{{ $mov->id }}
            </h2>
            <a href="{{ route('caja.index', array_filter(['desde'=>request('desde'),'hasta'=>request('hasta')])) }}"
               class="text-sm px-3 py-2 rounded border border-slate-300 dark:border-slate-700 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800">
                ← Volver
            </a>
        </div>
    </x-slot>

    @php
        // ✅ Sin "use" aquí; usar FQCN \Carbon\Carbon
        $formatDate = function($date) {
            if (!$date) return now()->toDateString();
            $c = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
            return $c->toDateString();
        };
        $money = fn($n) => '$'.number_format((int)($n ?? 0), 0, ',', '.');
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 rounded border border-red-800/40 bg-red-900/20 text-red-200 px-4 py-3">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('caja.update', $mov) }}"
                  class="bg-white dark:bg-black dark:text-slate-100 border border-slate-200 dark:border-slate-800 p-6 md:p-8 rounded-2xl shadow-lg space-y-6"
                  id="f-caja-edit">
                @csrf
                @method('PUT')

                {{-- Preservar filtros --}}
                @if(request()->filled('desde'))
                    <input type="hidden" name="__desde" value="{{ request('desde') }}">
                @endif
                @if(request()->filled('hasta'))
                    <input type="hidden" name="__hasta" value="{{ request('hasta') }}">
                @endif

                {{-- Fecha --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Fecha</label>
                    <input type="date" name="fecha"
                           value="{{ old('fecha', $formatDate($mov->fecha ?? $mov->created_at)) }}"
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                           required>
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Tipo</label>
                    <select name="tipo"
                            class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                            required>
                        <option value="ingreso" {{ old('tipo', $mov->tipo) === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                        <option value="egreso"  {{ old('tipo', $mov->tipo) === 'egreso'  ? 'selected' : '' }}>Egreso</option>
                    </select>
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Categoría</label>
                    <select name="categoria"
                            class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600">
                        @php
                            $cats = [
                                '' => '-- Selecciona --',
                                'gasolina' => 'Gasolina',
                                'comida' => 'Comida',
                                'peajes' => 'Peajes',
                                'otros_gastos' => 'Otros gastos',
                                'saldo_inicial' => 'Saldo inicial',
                                'venta_contado' => 'Venta (contado)',
                                'aporte_caja' => 'Aporte a caja',
                            ];
                            $catOld = old('categoria', $mov->categoria);
                        @endphp
                        @foreach($cats as $val => $label)
                            <option value="{{ $val }}" {{ $catOld === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Monto --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Monto</label>
                    <input type="text" name="monto" id="monto_edit"
                           value="{{ old('monto', $money($mov->monto)) }}"
                           placeholder="$0"
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600"
                           required>
                    <p class="text-xs text-slate-500 mt-1">Se formatea como COP; al enviar se normaliza a dígitos.</p>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Descripción</label>
                    <input type="text" name="descripcion" maxlength="200"
                           value="{{ old('descripcion', $mov->descripcion) }}"
                           class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600">
                </div>

                {{-- Observaciones (opcional) --}}
                <div>
                    <label class="block text-sm mb-1 dark:text-slate-300">Observaciones (opcional)</label>
                    <textarea name="observaciones" rows="3"
                              class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 px-3 py-2 focus:ring-2 focus:ring-indigo-600">{{ old('observaciones', $mov->observaciones) }}</textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('caja.index', array_filter(['desde'=>request('desde'),'hasta'=>request('hasta')])) }}"
                       class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Cancelar
                    </a>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Formato de dinero como COP + normalización al enviar --}}
    <script>
      (function(){
        const inp = document.getElementById('monto_edit');
        if(!inp) return;
        const digits = s => (s||'').replace(/[^\d]/g,'');
        const fmt    = n => '$' + (Number(n)||0).toLocaleString('es-CO');
        function onInput(e){
          const d = digits(e.target.value);
          e.target.value = d ? fmt(d) : '';
        }
        inp.addEventListener('input', onInput);

        document.getElementById('f-caja-edit').addEventListener('submit', ()=>{
          inp.value = digits(inp.value); // enviar solo números
        });
      })();
    </script>
</x-app-layout>
