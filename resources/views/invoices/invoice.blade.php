<p>{{ $msg }}</p>
<p>Factura {{ $invoice->prefix }}{{ $invoice->number }} — Total: ${{ number_format($invoice->grand_total,0,',','.') }}</p>
