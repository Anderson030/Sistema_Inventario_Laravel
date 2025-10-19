<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'tipo_grano',           // premium | eco
        'cantidad_bultos',
        'precio_por_bulto',
        'total',
        'fecha_compra',
        'observacion',
    ];

    protected $casts = [
        'fecha_compra' => 'date',   // para formatear en blade con ->format('d/m/Y')
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
}
