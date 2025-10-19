<?php
// app/Models/InvoiceItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
  protected $fillable = ['invoice_id','description','unit','qty','unit_price','line_total'];
  public function invoice(){ return $this->belongsTo(Invoice::class); }
}
