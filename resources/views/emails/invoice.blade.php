{{-- resources/views/emails/invoice.blade.php --}}
@php
  $fmt = fn($n) => number_format((int)$n, 0, ',', '.');
@endphp

<p style="margin:0 0 8px 0;">{{ $msg }}</p>

<p style="margin:0 0 4px 0;">
  <strong>Factura:</strong> {{ $invoice->prefix }}{{ $invoice->number }}<br>
  <strong>Fecha:</strong> {{ $invoice->issue_date? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d') }}<br>
  <strong>Total:</strong> ${{ $fmt($invoice->grand_total) }}<br>
  <strong>Pagado:</strong> ${{ $fmt($invoice->amount_paid) }}<br>
  <strong>Saldo:</strong> ${{ $fmt($invoice->balance_due) }}
</p>

<p style="margin-top:12px;">Adjuntamos el PDF de su factura. Gracias por su compra.</p>
