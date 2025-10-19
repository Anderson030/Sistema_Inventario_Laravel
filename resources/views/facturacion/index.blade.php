{{-- resources/views/facturacion/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
      Facturación — Buscar Envío
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      {{-- Alertas --}}
      @if(session('ok'))
        <div class="mb-3 p-3 rounded border border-emerald-800/40 bg-emerald-900/20 text-emerald-200">
          {{ session('ok') }}
        </div>
      @endif
      @if(session('error'))
        <div class="mb-3 p-3 rounded border border-red-800/40 bg-red-900/20 text-red-200">
          {{ session('error') }}
        </div>
      @endif
      @if($errors->any())
        <div class="mb-3 p-3 rounded border border-red-800/40 bg-red-900/20 text-red-200">
          <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- CONTENEDOR NEGRO --}}
      <div class="rounded-xl border dark:border-slate-800 bg-white dark:bg-black p-6">

        {{-- Buscador por ID de envío (centrado y más claro) --}}
        <form action="{{ route('facturacion.buscar') }}" method="GET"
              class="mx-auto mb-6 max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow px-4 py-4 flex items-center gap-3">
          <input type="number" min="1" name="envio_id"
                 class="flex-1 rounded border border-slate-300 dark:border-slate-700 px-3 py-2
                        bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"
                 placeholder="ID del envío"
                 value="{{ old('envio_id', request('envio_id')) }}">
          <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
            Buscar
          </button>
        </form>

        {{-- Resultado --}}
        @isset($envio)
          <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Envío #{{ $envio->id }}
              </h3>
              @if(isset($invoice))
                <span class="px-2 py-1 text-xs rounded bg-green-600 text-white">Con factura</span>
              @else
                <span class="px-2 py-1 text-xs rounded bg-slate-600 text-white">Sin factura</span>
              @endif
            </div>

            <div class="grid md:grid-cols-2 gap-4 text-slate-900 dark:text-slate-100">
              <div>
                <h4 class="font-semibold mb-2">Cliente</h4>
                <div class="text-sm">
                  <div><b>Nombre:</b> {{ $envio->cliente->nombre ?? '—' }}</div>
                  <div><b>Documento:</b> {{ $envio->cliente->documento ?? '—' }}</div>
                  <div><b>Email:</b> {{ $envio->cliente->email ?? '—' }}</div>
                  <div><b>Dirección:</b> {{ $envio->cliente->direccion ?? '—' }}</div>
                </div>
              </div>

              <div>
                <h4 class="font-semibold mb-2">Conductor</h4>
                <div class="text-sm">
                  <div><b>Nombre:</b> {{ $envio->conductor->nombre ?? '—' }}</div>
                  <div><b>Origen:</b> {{ $envio->origen ?? '—' }}</div>
                  <div><b>Destino:</b> {{ $envio->destino ?? '—' }}</div>
                  <div><b>Estado:</b> {{ $envio->estado ?? '—' }}</div>
                </div>
              </div>

              <div>
                <h4 class="font-semibold mb-2">Detalle del envío</h4>
                <div class="text-sm">
                  <div><b>Tipo de grano:</b> {{ $envio->tipo_grano ?? '—' }}</div>
                  <div><b>Bultos:</b> {{ $envio->numero_bulto ?? '—' }}</div>
                  <div><b>Valor por bulto:</b>
                    ${{ number_format((int)preg_replace('/[^\d]/','',(string)($envio->valor_bulto ?? 0)),0,',','.') }}
                  </div>
                  <div><b>Valor envío:</b>
                    ${{ number_format((int)preg_replace('/[^\d]/','',(string)($envio->valor_envio ?? 0)),0,',','.') }}
                  </div>
                  <div><b>Fecha envío:</b> {{ optional($envio->fecha_envio)->format('Y-m-d') ?? '—' }}</div>
                </div>
              </div>

              <div>
                <h4 class="font-semibold mb-2">Pagos del envío</h4>
                <div class="text-sm">
                  <div><b>Pago contado:</b>
                    ${{ number_format((int)preg_replace('/[^\d]/','',(string)($envio->pago_contado ?? 0)),0,',','.') }}
                  </div>
                  <div><b>Pago a plazo:</b>
                    ${{ number_format((int)preg_replace('/[^\d]/','',(string)($envio->pago_a_plazo ?? 0)),0,',','.') }}
                  </div>
                  <div><b>Fecha contado:</b> {{ optional($envio->fecha_contado)->format('Y-m-d') ?? '—' }}</div>
                  <div><b>Fecha plazo:</b> {{ optional($envio->fecha_plazo)->format('Y-m-d') ?? '—' }}</div>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap gap-2 mt-5">
              @isset($invoice)
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700">
                  Ver factura {{ $invoice->prefix }}{{ $invoice->number }}
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">
                  PDF
                </a>
                <form action="{{ route('invoices.email', $invoice) }}" method="POST">
                  @csrf
                  <input type="hidden" name="message" value="Hola {{ $invoice->customer_name }}, adjuntamos su factura.">
                  <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                    Enviar por email
                  </button>
                </form>
              @else
                <form action="{{ route('envios.facturar', $envio) }}" method="POST">
                  @csrf
                  <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                    Generar factura desde este envío
                  </button>
                </form>
              @endisset
            </div>
          </div>
        @endisset

      </div>
      {{-- /CONTENEDOR NEGRO --}}
    </div>
  </div>
</x-app-layout>
