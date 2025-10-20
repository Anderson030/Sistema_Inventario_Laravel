<?php
// app/Http/Controllers/InvoiceController.php
namespace App\Http\Controllers;

use App\Models\{Invoice, InvoiceItem, Envio};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;          // ← agregado
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
  /* =========================
   * LISTADO / CREAR MANUAL
   * ========================= */
  public function index(){
    $invoices = Invoice::latest()->paginate(15);
    return view('invoices.index', compact('invoices'));
  }

  public function create(){
    // Prefill demo de empresa MANYMUN S.A.S (NIT 900508008-5)
    $empresa = [
      'company_name'    => 'MANYMUN S A S',
      'company_nit'     => '900508008-5',
      'company_address' => 'CARRERA 3 #27-187, Antioquia',
      'company_phone'   => '',
      'company_email'   => '',
    ];
    return view('invoices.create', compact('empresa'));
  }

  public function store(Request $r){
    $data = $this->validatedInvoice($r);
    $next = (Invoice::max('number') ?? 0) + 1;

    $invoice = Invoice::create([
      'prefix'           => 'FE',
      'number'           => $next,
      'issue_date'       => now()->toDateString(),
      // empresa
      'company_name'     => $data['company_name'],
      'company_nit'      => $data['company_nit'],
      'company_address'  => $data['company_address'] ?? null,
      'company_phone'    => $data['company_phone'] ?? null,
      'company_email'    => $data['company_email'] ?? null,
      // cliente
      'customer_name'    => $data['customer_name'],
      'customer_doc'     => $data['customer_doc'] ?? null,
      'customer_email'   => $data['customer_email'] ?? null,
      'customer_address' => $data['customer_address'] ?? null,
    ]);

    // Items iniciales (opcional)
    foreach ($r->input('items',[]) as $it) {
      $qty   = (float)$it['qty'];
      $price = $this->digits($it['unit_price']); // "$120.000" -> 120000
      $invoice->items()->create([
        'description' => $it['description'],
        'unit'        => $it['unit'] ?? 'UND',
        'qty'         => $qty,
        'unit_price'  => $price,
        'line_total'  => (int) round($qty * $price),
      ]);
    }

    $invoice->recalcTotals();
    return redirect()->route('invoices.show',$invoice)->with('ok','Factura creada');
  }

  public function show(Invoice $invoice){
    $invoice->load(['items','payments']);
    return view('invoices.show', compact('invoice'));
  }

  /* =========================
   * ÍTEMS
   * ========================= */
  public function addItem(Request $r, Invoice $invoice){
    $r->validate([
      'description'=>['required','string'],
      'qty'        =>['required','numeric','min:0.01'],
      'unit_price' =>['required','string'],
      'unit'       =>['nullable','string']
    ]);

    $qty   = (float)$r->qty;
    $price = $this->digits($r->unit_price);

    $invoice->items()->create([
      'description'=>$r->description,
      'unit'=>$r->unit ?: 'UND',
      'qty'=>$qty,
      'unit_price'=>$price,
      'line_total'=>(int) round($qty * $price),
    ]);

    $invoice->recalcTotals();
    return back()->with('ok','Ítem agregado');
  }

  public function removeItem(Invoice $invoice, InvoiceItem $item){
    $item->delete();
    $invoice->recalcTotals();
    return back()->with('ok','Ítem eliminado');
  }

  /* =========================
   * ELIMINAR (con clave)
   * ========================= */
  public function destroy(Request $request, Invoice $invoice)
  {
    // Validación básica del campo
    $request->validate([
      'confirm_code' => ['required','string']
    ], [
      'confirm_code.required' => 'Debes ingresar la clave de confirmación.'
    ]);

    // Chequeo de clave (demo)
    if ($request->input('confirm_code') !== '123') {
      return back()->with('error', 'Clave de eliminación inválida.');
    }

    // Si no tienes cascada en BD, borra dependencias manualmente
    DB::transaction(function () use ($invoice) {
      if (method_exists($invoice, 'items')) {
        $invoice->items()->delete();
      }
      if (method_exists($invoice, 'payments')) {
        $invoice->payments()->delete();
      }
      $invoice->delete();
    });

    return redirect()->route('invoices.index')->with('ok', 'Factura eliminada correctamente.');
  }

  /* =========================
   * PDF + EMAIL
   * ========================= */
  public function pdf(Invoice $invoice){
    $invoice->load(['items','payments']);
    $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
    $file = 'invoices/'.$invoice->prefix.$invoice->number.'.pdf';
    Storage::disk('public')->put($file, $pdf->output());
    $invoice->update(['pdf_path'=>$file]);
    return response()->download(storage_path('app/public/'.$file));
  }

  public function sendEmail(Request $r, Invoice $invoice){
    if (!$invoice->pdf_path) { $this->pdf($invoice); $invoice->refresh(); }
    $msg = $r->input('message',"Hola {$invoice->customer_name}, adjuntamos su factura.");
    Mail::to($invoice->customer_email)->send(new \App\Mail\InvoiceMail($invoice,$msg));
    return back()->with('ok','Factura enviada a '.$invoice->customer_email);
  }

  /* ============================================================
   * MÓDULO DE FACTURACIÓN: BUSCADOR POR ID DE ENVÍO + GENERACIÓN
   * ============================================================ */

  // 1) Vista del buscador (facturación.index)
  public function finder(){
    return view('facturacion.index', ['envio'=>null, 'invoice'=>null]);
  }

  // 2) Buscar envío por ID y mostrar detalles (cliente, bultos, etc.)
  public function findByEnvio(Request $r){
    $r->validate(['envio_id' => ['required','integer','min:1']]);

    $envio = Envio::with(['cliente','conductor'])->find($r->envio_id);
    if (!$envio) {
      return back()->with('error','No se encontró el envío con ID '.$r->envio_id)
                   ->withInput();
    }

    $invoice = null;
    if (Schema::hasColumn('envios','invoice_id') && $envio->invoice_id) {
      $invoice = Invoice::find($envio->invoice_id);
    }

    return view('facturacion.index', compact('envio','invoice'));
  }

  // 3) Generar factura desde un Envío (si no existe) y redirigir a verla
  public function fromEnvio(Envio $envio)
  {
    // Si ya está relacionada y existe => ver
    if (Schema::hasColumn('envios','invoice_id') && $envio->invoice_id) {
      return redirect()->route('invoices.show', $envio->invoice_id);
    }

    $next = (Invoice::max('number') ?? 0) + 1;

    // Datos empresa (pon los tuyos definitivos)
    $company = [
      'company_name'    => 'MANYMUN S A S',
      'company_nit'     => '900508008-5',
      'company_address' => 'Antioquia',
      'company_phone'   => '',
      'company_email'   => '',
    ];

    // Cliente desde el envío si existe relación
    $clienteNombre  = $envio->cliente->nombre   ?? 'CLIENTE';
    $clienteDoc     = $envio->cliente->documento?? null;
    $clienteEmail   = $envio->cliente->email    ?? null;
    $clienteAddress = $envio->cliente->direccion?? null;

    // Crear factura
    $invoice = Invoice::create([
      'prefix'           => 'FE',
      'number'           => $next,
      'issue_date'       => now()->toDateString(),
      // empresa
      'company_name'     => $company['company_name'],
      'company_nit'      => $company['company_nit'],
      'company_address'  => $company['company_address'],
      'company_phone'    => $company['company_phone'],
      'company_email'    => $company['company_email'],
      // cliente
      'customer_name'    => $clienteNombre,
      'customer_doc'     => $clienteDoc,
      'customer_email'   => $clienteEmail,
      'customer_address' => $clienteAddress,
    ]);

    // Ítem principal desde el envío
    $qty   = $envio->numero_bulto ? (float)$envio->numero_bulto : 1.0;
    $price = $envio->valor_bulto
      ? (int) preg_replace('/[^\d]/','',(string)$envio->valor_bulto)
      : (int) preg_replace('/[^\d]/','',(string)($envio->valor_envio ?? 0));

    if ($price <= 0 && $envio->valor_envio) {
      $price = (int) preg_replace('/[^\d]/','',(string)$envio->valor_envio);
    }

    $invoice->items()->create([
      'description' => 'Servicio de transporte '.$envio->tipo_grano,
      'unit'        => $envio->numero_bulto ? 'BULTO' : 'UND',
      'qty'         => $qty,
      'unit_price'  => $price,
      'line_total'  => (int) round($qty * $price),
    ]);

    // Pago inicial si aplica
    $pagoInicial = 0;
    if (isset($envio->pago_contado) && $envio->pago_contado) {
      $pagoInicial = (int) preg_replace('/[^\d]/','',(string)$envio->pago_contado);
    } elseif (isset($envio->abono) && $envio->abono) {
      $pagoInicial = (int) preg_replace('/[^\d]/','',(string)$envio->abono);
    }
    if ($pagoInicial > 0 && method_exists($invoice, 'payments')) {
      $invoice->payments()->create([
        'paid_at' => now()->toDateString(),
        'amount'  => $pagoInicial,
        'method'  => 'TRASLADO DESDE ENVÍO',
        'note'    => 'Pago inicial al crear factura desde envío #'.$envio->id,
      ]);
    }

    // Recalcular totales y estado
    $invoice->recalcTotals();

    // Guardar relación si existe la columna invoice_id en envios
    if (Schema::hasColumn('envios','invoice_id')) {
      $envio->invoice_id = $invoice->id;
      $envio->save();
    }

    return redirect()->route('invoices.show', $invoice)->with('ok','Factura generada desde el envío #'.$envio->id);
  }

  /* =========================
   * HELPERS
   * ========================= */
  private function validatedInvoice(Request $r): array {
    return $r->validate([
      // empresa
      'company_name'    => ['required','string'],
      'company_nit'     => ['required','string'], // ej: 900508008-5
      'company_address' => ['nullable','string'],
      'company_phone'   => ['nullable','string'],
      'company_email'   => ['nullable','email'],
      // cliente
      'customer_name'    => ['required','string'],
      'customer_doc'     => ['nullable','string'],
      'customer_email'   => ['nullable','email'],
      'customer_address' => ['nullable','string'],
    ]);
  }

  private function digits($v): int {
    return (int) preg_replace('/[^\d]/','',(string)$v);
  }
}
