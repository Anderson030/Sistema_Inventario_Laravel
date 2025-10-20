{{-- resources/views/invoices/pdf.blade.php --}}
@php
    $fmt = fn($n) => number_format((int)$n, 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Factura {{ $invoice->prefix }}{{ $invoice->number }}</title>
  <style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 12px; margin: 24px; }
    h1,h2,h3,h4 { margin: 0 0 6px 0; }
    .muted { color:#555; }
    .row { display:flex; gap:18px; }
    .col { flex:1; }
    hr { border:0; border-top:1px solid #999; margin:10px 0; }
    table { border-collapse: collapse; width:100%; }
    th, td { border:1px solid #999; padding:6px; vertical-align: top; }
    th { background:#f2f2f2; text-align:left; }
    .no-border td { border:0; }
    .right { text-align:right; }
    .totals td { border:0; padding:3px 6px; }
    .totals .label { text-align:right; width:70%; }
    .totals .value { text-align:right; width:30%; }
    .footer { position: fixed; left:0; right:0; bottom: -10px; text-align:center; font-size:10px; color:#666; }
  </style>
</head>
<body>

  {{-- Encabezado --}}
  <table class="no-border" style="width:100%;">
    <tr class="no-border">
      <td class="no-border">
        <h3>Factura {{ $invoice->prefix }}{{ $invoice->number }}</h3>
        <div class="muted">Fecha: {{ $invoice->issue_date? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d') }}</div>
      </td>
      <td class="no-border right">
        {{-- Pon tu logo si quieres --}}
        {{-- <img src="{{ public_path('images/logo.png') }}" style="height:48px"> --}}
      </td>
    </tr>
  </table>

  <hr>

  {{-- Empresa --}}
  <div class="row">
    <div class="col">
      <strong>{{ $invoice->company_name }}</strong><br>
      NIT {{ $invoice->company_nit }}<br>
      @if($invoice->company_address) {{ $invoice->company_address }}<br>@endif
      @if($invoice->company_phone) Tel: {{ $invoice->company_phone }}<br>@endif
      @if($invoice->company_email) {{ $invoice->company_email }}<br>@endif
    </div>

    {{-- Cliente --}}
    <div class="col">
      <strong>Cliente</strong><br>
      {{ $invoice->customer_name }}<br>
      @if($invoice->customer_doc) Doc: {{ $invoice->customer_doc }}<br>@endif
      @if($invoice->customer_email) {{ $invoice->customer_email }}<br>@endif
      @if($invoice->customer_address) {{ $invoice->customer_address }}<br>@endif
    </div>
  </div>

  <hr>

  {{-- Items --}}
  <table>
    <thead>
      <tr>
        <th style="width:70px">Cant</th>
        <th>Descripción</th>
        <th style="width:120px" class="right">V/Unit</th>
        <th style="width:120px" class="right">Total</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoice->items as $it)
      <tr>
        <td>{{ rtrim(rtrim(number_format($it->qty,2,'.',''), '0'), '.') }}</td>
        <td>{{ $it->description }}</td>
        <td class="right">${{ $fmt($it->unit_price) }}</td>
        <td class="right">${{ $fmt($it->line_total) }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

  {{-- Totales --}}
  <table class="totals" style="margin-top:10px; width:100%;">
    <tr>
      <td class="label"><strong>Subtotal:</strong></td>
      <td class="value"><strong>${{ $fmt($invoice->subtotal) }}</strong></td>
    </tr>
    <tr>
      <td class="label">IVA:</td>
      <td class="value">${{ $fmt($invoice->tax_total) }}</td>
    </tr>
    <tr>
      <td class="label"><h3>Total:</h3></td>
      <td class="value"><h3>${{ $fmt($invoice->grand_total) }}</h3></td>
    </tr>
    <tr>
      <td class="label">Pagado:</td>
      <td class="value">${{ $fmt($invoice->amount_paid) }}</td>
    </tr>
    <tr>
      <td class="label"><strong>Saldo:</strong></td>
      <td class="value"><strong>${{ $fmt($invoice->balance_due) }}</strong></td>
    </tr>
    <tr>
      <td class="label">Estado:</td>
      <td class="value">{{ $invoice->status }}</td>
    </tr>
  </table>

  <div class="footer">
    Página {PAGE_NUM} de {PAGE_COUNT}
  </div>
</body>
</html>
