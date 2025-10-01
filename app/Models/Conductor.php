<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $table = 'conductores';

    protected $fillable = [
        'nombre','apellido','tipo_documento','documento','celular','foto','descripcion'
    ];

    public function envios()
    {
        return $this->hasMany(Envio::class, 'conductor_id');
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }
}
