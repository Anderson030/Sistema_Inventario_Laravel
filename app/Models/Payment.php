<?php
// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  protected $fillable = ['invoice_id','paid_at','amount','method','note'];
  protected $casts = ['paid_at'=>'date'];
  public function invoice(){ return $this->belongsTo(Invoice::class); }
}
