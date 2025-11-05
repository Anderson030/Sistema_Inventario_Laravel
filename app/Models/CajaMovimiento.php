<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class CajaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'fecha',
        'tipo',           // 'ingreso' | 'egreso'
        'categoria',      // ej: saldo_inicial, gasolina, venta_contado, etc.
        'descripcion',
        'monto',          // entero en pesos
        'venta_id',       // referencia (opcional) a envíos/ventas
        'compra_id',      // referencia (opcional) a compras
        'user_id',        // quién registró el movimiento (opcional)
        'observaciones',  // texto libre (opcional)
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /* =========================
       Relaciones (opcionales)
    ========================== */

    // Venta asociada (si aplica)
    public function venta()
    {
        return $this->belongsTo(Envio::class, 'venta_id');
    }

    // Compra asociada (si aplica)
    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    // Usuario que creó el movimiento (si usas auth)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =========================
       Scopes útiles
    ========================== */

    /**
     * Filtra por rango de fechas (YYYY-mm-dd)
     */
    public function scopeBetweenDates(Builder $q, ?string $desde, ?string $hasta): Builder
    {
        if ($desde && $hasta) {
            return $q->whereBetween('fecha', [$desde, $hasta]);
        }
        if ($desde) {
            return $q->whereDate('fecha', '>=', $desde);
        }
        if ($hasta) {
            return $q->whereDate('fecha', '<=', $hasta);
        }
        return $q;
    }

    /**
     * Solo ingresos
     */
    public function scopeIngresos(Builder $q): Builder
    {
        return $q->where('tipo', 'ingreso');
    }

    /**
     * Solo egresos
     */
    public function scopeEgresos(Builder $q): Builder
    {
        return $q->where('tipo', 'egreso');
    }
}
