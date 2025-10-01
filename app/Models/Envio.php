<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Envio extends Model
{
    protected $table = 'envios';

    protected $fillable = [
        'valor_envio','numero_bulto','valor_bulto','ganancia_total',
        'pago_contado','pago_a_plazo','fecha_contado','fecha_plazo','fecha_envio',
        'conductor_id','origen','destino','hora_salida','hora_llegada','estado',
    ];

    protected $casts = [
        'fecha_contado'=>'date','fecha_plazo'=>'date','fecha_envio'=>'date',
         'hora_salida'=>'datetime','hora_llegada'=>'datetime',
    ];

     public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }

    // Por si quieres calcular también desde PHP (además del generado en BD)
    public function getCostoSemanalAttribute()
    {
        if (!$this->fecha_envio || !$this->fecha_plazo) return null;
        $weeks = max(1, Carbon::parse($this->fecha_envio)->diffInWeeks($this->fecha_plazo));
        return $this->pago_a_plazo ? round($this->pago_a_plazo / $weeks, 2) : null;
    }
}
