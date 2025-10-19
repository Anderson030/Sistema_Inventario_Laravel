{{-- resources/views/invoices/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight">
      Facturas
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

      <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-slate-600 dark:text-slate-400">
          Mostrando {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} de {{ $invoices->total() }}
        </div>
        <a href="{{ route('invoices.create') }}"
           class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
          Nueva factura
        </a>
      </div>

      {{-- CONTENEDOR OSCURO + TABLA --}}
      <div class="overflow-x-auto rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 bg-white dark:bg-black">
        <table class="min-w-full border-t border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100">
          <thead class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100">
            <tr>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">#</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Fecha</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Cliente</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Total</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Pagado</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Saldo</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 text-left">Estado</th>
              <th class="p-2 border border-slate-200 dark:border-slate-800 w-64 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($invoices as $inv)
              <tr class="odd:bg-slate-900 even:bg-slate-950 hover:bg-slate-800 transition-colors">
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  {{ $inv->prefix }}{{ $inv->number }}
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  {{ optional($inv->issue_date)->format('Y-m-d') ?? $inv->created_at->format('Y-m-d') }}
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  {{ $inv->customer_name }}
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  ${{ number_format($inv->grand_total,0,',','.') }}
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  ${{ number_format($inv->amount_paid,0,',','.') }}
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  ${{ number_format($inv->balance_due,0,',','.') }}
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  <span class="px-2 py-1 rounded text-white text-xs
                    {{ $inv->status==='pagada' ? 'bg-green-600' : ($inv->status==='parcial' ? 'bg-amber-600' : 'bg-slate-600') }}">
                    {{ $inv->status }}
                  </span>
                </td>
                <td class="p-2 border border-slate-200 dark:border-slate-800">
                  <div class="flex flex-wrap gap-2">
                    {{-- Botones negros como en Clientes/Proveedores --}}
                    <a href="{{ route('invoices.show',$inv) }}"
                       class="px-3 py-1 rounded bg-black text-indigo-400 hover:bg-slate-800">
                      Ver
                    </a>

                    <a href="{{ route('invoices.pdf',$inv) }}"
                       class="px-3 py-1 rounded bg-black text-slate-300 hover:bg-slate-800">
                      PDF
                    </a>

                    @if($inv->customer_email)
                      <form action="{{ route('invoices.email',$inv) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="message"
                               value="Hola {{ $inv->customer_name }}, adjuntamos su factura.">
                        <button class="px-3 py-1 rounded bg-black text-emerald-400 hover:bg-slate-800">
                          Email
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr class="odd:bg-slate-900 even:bg-slate-950">
                <td class="p-2 border border-slate-200 dark:border-slate-800 text-center" colspan="8">
                  No hay facturas.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $invoices->links() }}
      </div>

    </div>
  </div>
</x-app-layout>
