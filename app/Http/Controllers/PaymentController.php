<?php
// app/Http/Controllers/PaymentController.php
namespace App\Http\Controllers;

use App\Models\{Invoice, Payment};
use Illuminate\Http\Request;

class PaymentController extends Controller
{
  public function store(Request $r, Invoice $invoice)
  {
    $r->validate([
      'paid_at' => ['required','date'],
      'amount'  => ['required','string'], // puede venir con $, puntos, comas
      'method'  => ['nullable','string'],
      'note'    => ['nullable','string','max:300'],
    ]);

    // Normaliza valor a enteros (pesos)
    $amount = (int) preg_replace('/[^\d]/','', (string)$r->amount);
    if ($amount <= 0) {
      return back()->with('error','El valor del pago debe ser mayor a 0')
                   ->withInput();
    }

    $invoice->payments()->create([
      'paid_at' => $r->paid_at,
      'amount'  => $amount,
      'method'  => $r->method,
      'note'    => $r->note,
    ]);

    $invoice->recalcTotals();
    return back()->with('ok','Pago registrado');
  }

  public function destroy(Invoice $invoice, Payment $payment)
  {
    // Seguridad: el pago debe pertenecer a la misma factura
    if ((int)$payment->invoice_id !== (int)$invoice->id) {
      return back()->with('error','Ese pago no pertenece a esta factura.');
    }

    $payment->delete();
    $invoice->recalcTotals();
    return back()->with('ok','Pago eliminado');
  }
}
