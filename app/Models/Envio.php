<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Envio extends Model
{
    protected $table = 'envios';

    // Sugerencia: no incluir ganancia_total si ya no la manejas
    protected $fillable = [
        'valor_envio',
        'numero_bulto',
        'valor_bulto',
        'pago_contado',
        'pago_a_plazo',
        'fecha_contado',
        'fecha_plazo',
        'fecha_envio',
        'conductor_id',
        'cliente_id',
        'tipo_grano',
        'origen',
        'destino',
        'hora_salida',
        'hora_llegada',
        'estado',
    ];

    protected $casts = [
        'fecha_contado' => 'date',
        'fecha_plazo'   => 'date',
        'fecha_envio'   => 'date',
        'hora_salida'   => 'datetime',
        'hora_llegada'  => 'datetime',
    ];

    // Valor por defecto útil
    protected $attributes = [
        'estado' => 'en_camino',
    ];

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Accesor útil si alguna vez lo necesitas
    public function getCostoSemanalAttribute()
    {
        if (!$this->fecha_envio || !$this->fecha_plazo) return null;
        $weeks = max(1, Carbon::parse($this->fecha_envio)->diffInWeeks($this->fecha_plazo));
        return $this->pago_a_plazo ? round($this->pago_a_plazo / $weeks, 2) : null;
    }
}
