<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar envío #{{ $envio->id }}</h2></x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('envios.update',$envio) }}" class="bg-white p-6 rounded shadow space-y-4" id="formEnvio">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input-label value="valor envio" />
                    <x-text-input name="valor_envio" type="text" class="md:col-span-2 cop"
                        value="{{ number_format($envio->valor_envio,0,',','.') }}" required />

                    <x-input-label value="numero bulto" />
                    <x-text-input name="numero_bulto" type="number" min="1" class="md:col-span-2"
                        value="{{ $envio->numero_bulto }}" required />

                    <x-input-label value="valor bulto" />
                    <x-text-input name="valor_bulto" type="text" class="md:col-span-2 cop"
                        value="{{ number_format($envio->valor_bulto,0,',','.') }}" required />

                    <x-input-label value="ganacia total" />
                    <x-text-input name="ganancia_total" type="text" class="md:col-span-2 cop"
                        value="{{ number_format($envio->ganancia_total,0,',','.') }}" required />

                    <x-input-label value="pago contado" />
                    <x-text-input name="pago_contado" type="text" class="md:col-span-2 cop"
                        value="{{ number_format($envio->pago_contado,0,',','.') }}" />

                    <x-input-label value="pago a plazo" />
                    <x-text-input name="pago_a_plazo" type="text" class="md:col-span-2 cop"
                        value="{{ number_format($envio->pago_a_plazo,0,',','.') }}" />

                    <x-input-label value="fecha contado" />
                    <x-text-input name="fecha_contado" type="date" class="md:col-span-2"
                        value="{{ optional($envio->fecha_contado)->format('Y-m-d') }}" />

                    <x-input-label value="fecha plazo" />
                    <x-text-input name="fecha_plazo" type="date" class="md:col-span-2"
                        value="{{ optional($envio->fecha_plazo)->format('Y-m-d') }}" />

                    <x-input-label value="fecha envio" />
                    <x-text-input name="fecha_envio" type="date" class="md:col-span-2"
                        value="{{ optional($envio->fecha_envio)->format('Y-m-d') }}" />
                </div>

                <div class="flex gap-2 justify-end">
                    <a href="{{ route('envios.index') }}" class="px-4 py-2 border rounded">Volver</a>
                    <x-primary-button>Actualizar</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    {{-- mismo script de formato COP que en create --}}
    <script>
    (function(){
        const fmt = new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', maximumFractionDigits: 0 });
        function unformatCop(str){ return (str||'').replace(/[^\d]/g,''); }
        function formatCopInput(el){
            const raw = unformatCop(el.value);
            el.value = raw ? fmt.format(parseInt(raw,10)) : '';
        }
        document.querySelectorAll('input.cop').forEach(el=>{
            el.addEventListener('input', ()=>formatCopInput(el));
            el.addEventListener('blur', ()=>formatCopInput(el));
        });
        document.getElementById('formEnvio').addEventListener('submit', ()=>{
            document.querySelectorAll('input.cop').forEach(el=> el.value = unformatCop(el.value));
        });
    })();
    </script>
</x-app-layout>
