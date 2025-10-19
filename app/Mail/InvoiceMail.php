<?php
// app/Mail/InvoiceMail.php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use App\Models\Invoice;

class InvoiceMail extends Mailable
{
  public function __construct(public Invoice $invoice, public string $msg) {}

  public function build(){
    $m = $this->subject('Factura '.$this->invoice->prefix.$this->invoice->number)
             ->view('emails.invoice',[
               'invoice'=>$this->invoice,
               'msg'=>$this->msg
             ]);
    if ($this->invoice->pdf_path) {
      $m->attach(storage_path('app/public/'.$this->invoice->pdf_path));
    }
    return $m;
  }
}
