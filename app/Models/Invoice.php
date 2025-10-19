<?php
// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
  protected $fillable = [
    'prefix','number','issue_date',
    'company_name','company_nit','company_address','company_phone','company_email',
    'customer_name','customer_doc','customer_email','customer_address',
    'subtotal','tax_total','grand_total','amount_paid','balance_due','status','pdf_path'
  ];
  protected $casts = ['issue_date'=>'date'];

  public function items(){ return $this->hasMany(InvoiceItem::class); }
  public function payments(){ return $this->hasMany(Payment::class); }

  // Recalcula totales/estado (sin POO extra)
  public function recalcTotals(): void
  {
    $this->subtotal    = (int) $this->items()->sum('line_total');
    $this->tax_total   = (int) 0; // si vas a usar IVA: $this->subtotal * 0.19;
    $this->grand_total = (int) ($this->subtotal + $this->tax_total);
    $this->amount_paid = (int) $this->payments()->sum('amount');
    $this->balance_due = max(0, $this->grand_total - $this->amount_paid);

    $this->status = $this->amount_paid <= 0
      ? 'OPEN'
      : ($this->balance_due > 0 ? 'PARTIALLY_PAID' : 'PAID');

    $this->save();
  }
}
