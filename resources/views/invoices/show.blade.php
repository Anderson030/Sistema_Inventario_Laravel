{{-- resources/views/invoices/show.blade.php --}}
@extends('layouts.app') {{-- Cambia si tu layout tiene otro nombre --}}

@section('content')
<div class="container">

  <h2 class="mb-2">
    Factura {{ $invoice->prefix }}{{ $invoice->number }}
    <small class="text-muted">— {{ $invoice->status }}</small>
  </h2>

  {{-- Alertas --}}
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger mb-2">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <p><b>{{ $invoice->company_name }}</b> — NIT {{ $invoice->company_nit }}</p>
  <p>
    {{ $invoice->company_address }}
    @if($invoice->company_phone) | {{ $invoice->company_phone }} @endif
    @if($invoice->company_email) | {{ $invoice->company_email }} @endif
  </p>
  <hr>
  <p><b>Cliente:</b> {{ $invoice->customer_name }}
     @if($invoice->customer_doc) ({{ $invoice->customer_doc }}) @endif
     @if($invoice->customer_email) — {{ $invoice->customer_email }} @endif
  </p>
  @if($invoice->customer_address)
    <p>{{ $invoice->customer_address }}</p>
  @endif

  {{-- Ítems --}}
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th style="width:100px">Cant</th>
          <th>Descripción</th>
          <th style="width:160px">V/Unit</th>
          <th style="width:160px">Total</th>
          <th style="width:60px"></th>
        </tr>
      </thead>
      <tbody>
      @foreach($invoice->items as $it)
        <tr>
          <td>{{ rtrim(rtrim(number_format($it->qty,2,'.',''), '0'), '.') }}</td>
          <td>{{ $it->description }}</td>
          <td>${{ number_format($it->unit_price,0,',','.') }}</td>
          <td>${{ number_format($it->line_total,0,',','.') }}</td>
          <td>
            <form action="{{ route('invoices.items.remove',[$invoice,$it]) }}" method="POST">
              @csrf @method('DELETE')
              <button class="btn btn-link btn-sm" title="Eliminar">🗑️</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  {{-- Agregar ítem --}}
  <form action="{{ route('invoices.items.add',$invoice) }}" method="POST" class="row g-2 mb-4">
    @csrf
    <div class="col-md-5">
      <input name="description" class="form-control" placeholder="Descripción" required>
    </div>
    <div class="col-md-2">
      <input name="qty" type="number" step="0.01" class="form-control" placeholder="Cant." required>
    </div>
    <div class="col-md-3">
      <input name="unit_price" class="form-control" placeholder="V/Unit (ej: $120.000)" required>
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-success btn-sm">Agregar ítem</button>
    </div>
    <div class="col-12">
      <input name="unit" class="form-control form-control-sm w-auto" placeholder="UND/KG" value="UND">
    </div>
  </form>

  {{-- Totales --}}
  <div class="mb-3">
    <p>Subtotal: <b>${{ number_format($invoice->subtotal,0,',','.') }}</b></p>
    <p>IVA: <b>${{ number_format($invoice->tax_total,0,',','.') }}</b></p>
    <h4>Total: ${{ number_format($invoice->grand_total,0,',','.') }}</h4>
    <h4>Pagado: ${{ number_format($invoice->amount_paid,0,',','.') }}</h4>
    <h3>Saldo: ${{ number_format($invoice->balance_due,0,',','.') }}</h3>
  </div>

  {{-- Pagos parciales --}}
  <h4 class="mt-4">Pagos</h4>
  @if($invoice->payments->count())
    <ul class="list-unstyled">
      @foreach($invoice->payments as $p)
        <li class="mb-1">
          {{ $p->paid_at->format('Y-m-d') }} — ${{ number_format($p->amount,0,',','.') }}
          @if($p->method) ({{ $p->method }}) @endif
          <form action="{{ route('invoices.payments.destroy',[$invoice,$p]) }}"
                method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-link btn-sm">Eliminar</button>
          </form>
        </li>
      @endforeach
    </ul>
  @else
    <p class="text-muted">Aún no hay pagos registrados.</p>
  @endif

  <form action="{{ route('invoices.payments.store',$invoice) }}" method="POST" class="row g-2 mb-4">
    @csrf
    <div class="col-md-3">
      <input type="date" name="paid_at" class="form-control" value="{{ now()->toDateString() }}" required>
    </div>
    <div class="col-md-3">
      <input name="amount" class="form-control" placeholder="Valor (ej: $300.000)" required>
    </div>
    <div class="col-md-3">
      <input name="method" class="form-control" placeholder="Método (opcional)">
    </div>
    <div class="col-md-3 d-grid">
      <button class="btn btn-primary btn-sm">Agregar pago</button>
    </div>
    <div class="col-12">
      <input name="note" class="form-control form-control-sm" placeholder="Nota (opcional)">
    </div>
  </form>

  {{-- Acciones PDF / Email --}}
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('invoices.pdf',$invoice) }}">
      Generar/Descargar PDF
    </a>

    <form action="{{ route('invoices.email',$invoice) }}" method="POST" class="d-inline">
      @csrf
      <input type="hidden" name="message" value="Hola {{ $invoice->customer_name }}, adjuntamos su factura.">
      <button class="btn btn-primary btn-sm">Enviar por email</button>
    </form>
  </div>

</div>
@endsection
