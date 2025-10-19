<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo envío</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('envios.store') }}" class="bg-white p-6 rounded shadow space-y-4" id="f-envio">
                @csrf

                {{-- Cliente --}}
                <div>
                    <label class="block text-sm mb-1">Cliente</label>
                    <select name="cliente_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">Seleccione…</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bultos / Valor por bulto / Total --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Bultos a vender</label>
                        <input type="number" min="1" name="numero_bulto" id="e_bultos" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Valor por bulto</label>
                        <input type="text" name="valor_bulto" id="e_valor_bulto" class="w-full border rounded px-3 py-2" placeholder="$0" required>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Total</label>
                        <input type="text" id="e_total" class="w-full border rounded px-3 py-2 bg-slate-50" readonly>
                    </div>
                </div>

                {{-- Tipo de grano --}}
                <div>
                    <label class="block text-sm mb-1">Tipo de grano</label>
                    <select name="tipo_grano" class="w-full border rounded px-3 py-2" required>
                        <option value="premium">Premium</option>
                        <option value="eco">Eco</option>
                    </select>
                </div>

                {{-- Abono / Saldo / Fecha envío / Fecha plazo --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Abono</label>
                        <input type="text" name="pago_contado" id="e_abono" class="w-full border rounded px-3 py-2" placeholder="$0">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Queda debiendo</label>
                        <input type="text" id="e_saldo" class="w-full border rounded px-3 py-2 bg-slate-50" readonly>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fecha de envío</label>
                        <input type="date" name="fecha_envio" value="{{ now()->toDateString() }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fecha de plazo</label>
                        <input type="date" name="fecha_plazo" class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('envios.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                    <x-primary-button>Guardar</x-primary-button>
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

        // Antes de enviar, normaliza a dígitos lo que va al backend
        document.getElementById('f-envio').addEventListener('submit', ()=>{
            if (valor) valor.value = digits(valor.value);
            if (abono) abono.value = digits(abono.value);
        });
    })();
    </script>
</x-app-layout>
